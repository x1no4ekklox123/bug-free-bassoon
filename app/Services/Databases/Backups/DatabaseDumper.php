<?php

namespace Pterodactyl\Services\Databases\Backups;

use Carbon\CarbonImmutable;
use Pterodactyl\Models\Database;

/**
 * Creates a gzip compressed SQL dump of a server database without depending on the
 * mysqldump binary. The output can be imported with the regular mysql client as well.
 */
class DatabaseDumper
{
    // Approximate maximum size of a single INSERT statement.
    private const INSERT_BATCH_BYTES = 1024 * 1024;

    private const BINARY_TYPES = [
        'binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob',
        'geometry', 'point', 'linestring', 'polygon', 'multipoint', 'multilinestring',
        'multipolygon', 'geometrycollection', 'geomcollection',
    ];

    private const NUMERIC_TYPES = [
        'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint',
        'decimal', 'numeric', 'float', 'double', 'real', 'year',
    ];

    /** @var resource|null */
    private $file;

    public function __construct(private BackupConnectionFactory $connections)
    {
    }

    /**
     * @throws \Throwable
     */
    public function dump(Database $database, string $path): void
    {
        $file = gzopen($path, 'wb6');
        if ($file === false) {
            throw new \RuntimeException('Unable to open the temporary dump file for writing.');
        }

        $this->file = $file;

        try {
            $pdo = $this->connections->open($database);
            $schema = $database->database;

            $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

            $this->writeHeader($pdo, $schema);

            $objects = $this->fetchAll($pdo, 'SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME', [$schema]);

            $tables = array_values(array_map(fn ($row) => $row[0], array_filter($objects, fn ($row) => $row[1] === 'BASE TABLE')));
            $views = array_values(array_map(fn ($row) => $row[0], array_filter($objects, fn ($row) => $row[1] === 'VIEW')));

            foreach ($tables as $table) {
                $this->dumpTable($pdo, $schema, $table);
            }

            foreach ($views as $view) {
                $this->dumpView($pdo, $view);
            }

            $this->dumpTriggers($pdo, $schema);
            $this->dumpRoutines($pdo, $schema);

            $pdo->exec('COMMIT');

            $this->write("SET FOREIGN_KEY_CHECKS = 1;\nSET UNIQUE_CHECKS = 1;\n\n-- Dump completed at " . CarbonImmutable::now('UTC')->toIso8601String() . "\n");
        } finally {
            $this->connections->close();
            gzclose($file);
            $this->file = null;
        }
    }

    protected function writeHeader(\PDO $pdo, string $schema): void
    {
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();

        $this->write(implode("\n", [
            '-- Pterodactyl database backup',
            '-- Database: ' . $this->identifier($schema),
            '-- Server version: ' . str_replace(["\r", "\n"], '', (string) $version),
            '-- Created at: ' . CarbonImmutable::now('UTC')->toIso8601String(),
            '',
            'SET NAMES utf8mb4;',
            "SET time_zone = '+00:00';",
            'SET FOREIGN_KEY_CHECKS = 0;',
            'SET UNIQUE_CHECKS = 0;',
            "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';",
            '',
            '',
        ]));
    }

    protected function dumpTable(\PDO $pdo, string $schema, string $table): void
    {
        $create = $pdo->query('SHOW CREATE TABLE ' . $this->identifier($table))->fetch(\PDO::FETCH_NUM)[1];

        $this->write("--\n-- Table structure for " . $this->identifier($table) . "\n--\n\n");
        $this->write('DROP TABLE IF EXISTS ' . $this->identifier($table) . ";\n");
        $this->write($create . ";\n\n");

        $columns = $this->fetchAll(
            $pdo,
            'SELECT COLUMN_NAME, DATA_TYPE, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$schema, $table]
        );

        // Generated columns cannot be inserted into, their values are recalculated on restore.
        $columns = array_values(array_filter($columns, fn ($column) => !preg_match('/\b(VIRTUAL|STORED|PERSISTENT) GENERATED\b/i', (string) $column[2])));
        if (empty($columns)) {
            return;
        }

        $select = [];
        $types = [];
        foreach ($columns as [$name, $type]) {
            $type = strtolower((string) $type);
            $types[] = $type;
            $select[] = $type === 'bit' ? 'CAST(' . $this->identifier($name) . ' AS UNSIGNED)' : $this->identifier($name);
        }

        $prefix = 'INSERT INTO ' . $this->identifier($table)
            . ' (' . implode(', ', array_map(fn ($column) => $this->identifier($column[0]), $columns)) . ') VALUES ';

        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        try {
            $statement = $pdo->query('SELECT ' . implode(', ', $select) . ' FROM ' . $this->identifier($table));

            $buffer = '';
            $written = false;
            while (($row = $statement->fetch(\PDO::FETCH_NUM)) !== false) {
                $values = [];
                foreach ($row as $index => $value) {
                    $values[] = $this->value($value, $types[$index]);
                }

                $tuple = '(' . implode(',', $values) . ')';
                if ($buffer !== '' && strlen($buffer) + strlen($tuple) > self::INSERT_BATCH_BYTES) {
                    $this->write($buffer . ";\n");
                    $buffer = '';
                }

                $buffer .= ($buffer === '' ? $prefix : ',') . $tuple;
                $written = true;
            }

            $statement->closeCursor();

            if ($buffer !== '') {
                $this->write($buffer . ";\n");
            }

            if ($written) {
                $this->write("\n");
            }
        } finally {
            $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }
    }

    protected function dumpView(\PDO $pdo, string $view): void
    {
        $create = $pdo->query('SHOW CREATE VIEW ' . $this->identifier($view))->fetch(\PDO::FETCH_NUM)[1];

        $this->write("--\n-- View structure for " . $this->identifier($view) . "\n--\n\n");
        $this->write('DROP TABLE IF EXISTS ' . $this->identifier($view) . ";\n");
        $this->write('DROP VIEW IF EXISTS ' . $this->identifier($view) . ";\n");
        $this->write(DefinerGuard::strip($create) . ";\n\n");
    }

    protected function dumpTriggers(\PDO $pdo, string $schema): void
    {
        $triggers = $this->fetchAll($pdo, 'SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ? ORDER BY EVENT_OBJECT_TABLE, ACTION_ORDER', [$schema]);

        foreach ($triggers as [$name]) {
            $row = $pdo->query('SHOW CREATE TRIGGER ' . $this->identifier($name))->fetch(\PDO::FETCH_ASSOC);
            if (empty($row['SQL Original Statement'])) {
                continue;
            }

            $this->write("--\n-- Trigger " . $this->identifier($name) . "\n--\n\n");
            $this->write('SET SESSION sql_mode = ' . $this->quote((string) $row['sql_mode']) . ";\n");
            $this->write('DROP TRIGGER IF EXISTS ' . $this->identifier($name) . ";\n");
            $this->write("DELIMITER ;;\n" . DefinerGuard::strip($row['SQL Original Statement']) . " ;;\nDELIMITER ;\n");
            $this->write("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';\n\n");
        }
    }

    protected function dumpRoutines(\PDO $pdo, string $schema): void
    {
        $routines = $this->fetchAll($pdo, 'SELECT ROUTINE_NAME, ROUTINE_TYPE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ? ORDER BY ROUTINE_TYPE, ROUTINE_NAME', [$schema]);

        foreach ($routines as [$name, $type]) {
            $type = strtoupper((string) $type);
            if (!in_array($type, ['PROCEDURE', 'FUNCTION'], true)) {
                continue;
            }

            $row = $pdo->query("SHOW CREATE $type " . $this->identifier($name))->fetch(\PDO::FETCH_ASSOC);
            $create = $row['Create ' . ucfirst(strtolower($type))] ?? null;
            if (empty($create)) {
                continue;
            }

            $this->write("--\n-- " . ucfirst(strtolower($type)) . ' ' . $this->identifier($name) . "\n--\n\n");
            $this->write('SET SESSION sql_mode = ' . $this->quote((string) $row['sql_mode']) . ";\n");
            $this->write("DROP $type IF EXISTS " . $this->identifier($name) . ";\n");
            $this->write("DELIMITER ;;\n" . DefinerGuard::strip($create) . " ;;\nDELIMITER ;\n");
            $this->write("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';\n\n");
        }
    }

    protected function value(?string $value, string $type): string
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (in_array($type, self::BINARY_TYPES, true)) {
            return $value === '' ? "''" : '0x' . bin2hex($value);
        }

        if (($type === 'bit' || in_array($type, self::NUMERIC_TYPES, true)) && is_numeric($value)) {
            return $value;
        }

        return $this->quote($value);
    }

    /**
     * Quotes a string literal. Escaping is done here instead of PDO::quote() so the
     * output never depends on the server's NO_BACKSLASH_ESCAPES mode.
     */
    protected function quote(string $value): string
    {
        return "'" . strtr($value, [
            '\\' => '\\\\',
            "\0" => '\\0',
            "\n" => '\\n',
            "\r" => '\\r',
            "'" => "\\'",
            '"' => '\\"',
            "\x1a" => '\\Z',
        ]) . "'";
    }

    protected function identifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    protected function fetchAll(\PDO $pdo, string $sql, array $bindings = []): array
    {
        $statement = $pdo->prepare($sql);
        $statement->execute($bindings);

        return $statement->fetchAll(\PDO::FETCH_NUM);
    }

    protected function write(string $data): void
    {
        if (gzwrite($this->file, $data) === false) {
            throw new \RuntimeException('Failed to write to the temporary dump file.');
        }
    }
}

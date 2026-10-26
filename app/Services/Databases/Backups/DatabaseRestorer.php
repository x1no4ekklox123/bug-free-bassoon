<?php

namespace Pterodactyl\Services\Databases\Backups;

use Pterodactyl\Models\Database;

class DatabaseRestorer
{
    public function __construct(private BackupConnectionFactory $connections)
    {
    }

    /**
     * Replaces the contents of the database with the given gzip compressed dump.
     *
     * @throws \Throwable
     */
    public function restore(Database $database, string $path): void
    {
        $guard = SqlStatementGuard::forDatabase($database);

        try {
            $pdo = $this->connections->open($database);
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

            $this->dropExistingObjects($pdo, $database->database);

            foreach (SqlStatementReader::fromGzipFile($path) as $statement) {
                $pdo->exec($guard->secure($statement));
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        } finally {
            $this->connections->close();
        }
    }

    /**
     * Removes all tables, views and stored routines so that the database matches the
     * backup exactly once the restore has finished.
     */
    protected function dropExistingObjects(\PDO $pdo, string $schema): void
    {
        $statement = $pdo->prepare('SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?');
        $statement->execute([$schema]);

        foreach ($statement->fetchAll(\PDO::FETCH_NUM) as [$name, $type]) {
            $keyword = $type === 'VIEW' ? 'VIEW' : 'TABLE';
            $pdo->exec("DROP $keyword IF EXISTS " . $this->identifier($name));
        }

        $statement = $pdo->prepare('SELECT ROUTINE_NAME, ROUTINE_TYPE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ?');
        $statement->execute([$schema]);

        foreach ($statement->fetchAll(\PDO::FETCH_NUM) as [$name, $type]) {
            if (in_array(strtoupper($type), ['PROCEDURE', 'FUNCTION'], true)) {
                $pdo->exec('DROP ' . strtoupper($type) . ' IF EXISTS ' . $this->identifier($name));
            }
        }
    }

    protected function identifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }
}

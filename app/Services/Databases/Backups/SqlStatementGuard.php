<?php

namespace Pterodactyl\Services\Databases\Backups;

use Pterodactyl\Models\Database;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Restores run with the database host's administrative account, so every statement of
 * a dump is checked against a strict allow-list before it is executed. Stored programs
 * and views always get the server database's own account as DEFINER, otherwise they
 * would execute with the administrative account's privileges.
 */
class SqlStatementGuard
{
    private const IDENT = '`(?:[^`]|``)+`';

    // DEFINER = user@host in any of the forms MySQL and MariaDB print or accept.
    private const DEFINER = '(?:DEFINER\s*=\s*(?:CURRENT_USER(?:\s*\(\s*\))?|(?:`(?:[^`]|``)*`|\'(?:[^\'\\\\]|\\\\.|\'\')*\'|[^\s@`\']+)(?:\s*@\s*(?:`(?:[^`]|``)*`|\'(?:[^\'\\\\]|\\\\.|\'\')*\'|[^\s`\']+))?)\s+)';

    private const UNSAFE_SQL_MODES = ['NO_BACKSLASH_ESCAPES', 'ANSI_QUOTES', 'ANSI'];

    public function __construct(private string $definer)
    {
    }

    public static function forDatabase(Database $database): self
    {
        return new self(sprintf(
            '%s@%s',
            '`' . str_replace('`', '``', $database->username) . '`',
            '`' . str_replace('`', '``', $database->remote) . '`',
        ));
    }

    /**
     * Removes the DEFINER clause from a CREATE statement returned by SHOW CREATE ...
     */
    public static function stripDefiner(string $statement): string
    {
        return preg_replace(
            '/^(\s*CREATE\s+(?:OR\s+REPLACE\s+)?(?:ALGORITHM\s*=\s*\w+\s+)?)' . self::DEFINER . '/i',
            '$1',
            $statement,
            1
        ) ?? $statement;
    }

    /**
     * Returns true if statements created with the given sql_mode could be parsed differently
     * by the server than by the dump reader.
     */
    public static function isUnsafeSqlMode(string $mode): bool
    {
        $modes = array_map('trim', explode(',', strtoupper($mode)));

        return count(array_intersect($modes, self::UNSAFE_SQL_MODES)) > 0;
    }

    /**
     * Returns the statement that should be executed or throws if it is not allowed.
     *
     * @throws DisplayException
     */
    public function secure(string $statement): string
    {
        $statement = trim($statement);
        $ident = self::IDENT;

        $allowed = [
            '/^SET\s+NAMES\s+\'?utf8mb4\'?(?:\s+COLLATE\s+\'?\w+\'?)?$/i',
            '/^SET\s+(?:SESSION\s+)?time_zone\s*=\s*\'[+\-]\d{2}:\d{2}\'$/i',
            '/^SET\s+(?:SESSION\s+)?(?:FOREIGN_KEY_CHECKS|UNIQUE_CHECKS)\s*=\s*[01]$/i',
            "/^DROP\s+(?:TABLE|VIEW|TRIGGER|PROCEDURE|FUNCTION)\s+IF\s+EXISTS\s+$ident$/i",
            "/^CREATE\s+TABLE\s+$ident\s*\(/i",
            "/^INSERT\s+INTO\s+$ident\s*\(\s*$ident(?:\s*,\s*$ident)*\s*\)\s*VALUES\s*\(/i",
        ];

        foreach ($allowed as $pattern) {
            if (preg_match($pattern, $statement)) {
                return $statement;
            }
        }

        if (preg_match('/^SET\s+(?:SESSION\s+)?sql_mode\s*=\s*\'([A-Z0-9_,]*)\'$/i', $statement, $matches)) {
            if (!self::isUnsafeSqlMode($matches[1])) {
                return $statement;
            }
        }

        $definer = self::DEFINER;

        // CREATE [OR REPLACE] [ALGORITHM = x] [DEFINER = x] [SQL SECURITY x] VIEW `name`
        $view = "/^CREATE\s+(OR\s+REPLACE\s+)?(ALGORITHM\s*=\s*\w+\s+)?$definer?((?:SQL\s+SECURITY\s+(?:DEFINER|INVOKER)\s+)?VIEW\s+$ident\s)/i";
        if (preg_match($view, $statement, $matches)) {
            return 'CREATE ' . $matches[1] . $matches[2] . 'DEFINER=' . $this->definer . ' ' . substr($statement, strlen($matches[0]) - strlen($matches[3]));
        }

        // CREATE [DEFINER = x] {TRIGGER|PROCEDURE|FUNCTION} `name`
        $program = "/^CREATE\s+$definer?((?:TRIGGER|PROCEDURE|FUNCTION)\s+$ident)/i";
        if (preg_match($program, $statement, $matches)) {
            return 'CREATE DEFINER=' . $this->definer . ' ' . substr($statement, strlen($matches[0]) - strlen($matches[1]));
        }

        throw new DisplayException('The backup contains an unsupported SQL statement and cannot be restored.');
    }
}

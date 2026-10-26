<?php

namespace Pterodactyl\Services\Databases\Backups;

use Pterodactyl\Models\Database;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

/**
 * Opens a connection to the database host of a server database using the host's
 * administrative credentials. Unlike DynamicDatabaseConnection this always uses
 * utf8mb4 so that dumps do not lose any data.
 */
class BackupConnectionFactory
{
    public const CONNECTION = 'pterodactyl_database_backups';

    public function __construct(
        private ConfigRepository $config,
        private DatabaseManager $manager,
        private Encrypter $encrypter,
    ) {
    }

    public function open(Database $database): \PDO
    {
        $host = $database->host()->firstOrFail();

        $this->config->set('database.connections.' . self::CONNECTION, [
            'driver' => 'mysql',
            'host' => $host->host,
            'port' => $host->port,
            'database' => $database->database,
            'username' => $host->username,
            'password' => $this->encrypter->decrypt($host->password),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'options' => [
                \PDO::ATTR_TIMEOUT => 10,
                \PDO::ATTR_EMULATE_PREPARES => true,
                \PDO::ATTR_STRINGIFY_FETCHES => true,
                // Restores rely on every query containing exactly one statement.
                \PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
            ],
        ]);

        $this->manager->purge(self::CONNECTION);

        $pdo = $this->manager->connection(self::CONNECTION)->getPdo();
        // Backslash escaping is relied upon by the dump format, so the session mode is pinned.
        $pdo->exec("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        $pdo->exec("SET SESSION time_zone = '+00:00'");
        $pdo->exec('SET SESSION net_read_timeout = 3600, net_write_timeout = 3600, wait_timeout = 28800');

        return $pdo;
    }

    public function close(): void
    {
        $this->manager->purge(self::CONNECTION);
    }
}

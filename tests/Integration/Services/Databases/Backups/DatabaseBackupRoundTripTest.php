<?php

namespace Pterodactyl\Tests\Integration\Services\Databases\Backups;

use Pterodactyl\Models\Database;
use Pterodactyl\Models\DatabaseHost;
use Pterodactyl\Tests\Integration\IntegrationTestCase;
use Pterodactyl\Services\Databases\Backups\DatabaseDumper;
use Pterodactyl\Services\Databases\Backups\DatabaseRestorer;
use Pterodactyl\Services\Databases\DatabaseManagementService;

/**
 * Dumps and restores a real database on the test database server.
 */
class DatabaseBackupRoundTripTest extends IntegrationTestCase
{
    private ?Database $database = null;

    private ?string $path = null;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('pterodactyl.client_features.databases.enabled', true);

        $connection = config('database.connections.mysql');
        $host = DatabaseHost::factory()->create([
            'host' => $connection['host'],
            'port' => $connection['port'],
            'username' => $connection['username'],
            'password' => encrypt($connection['password'] ?? ''),
        ]);

        $server = $this->createServerModel();
        $this->database = $this->app->make(DatabaseManagementService::class)->create($server, [
            'database_host_id' => $host->id,
            'database' => DatabaseManagementService::generateUniqueDatabaseName('roundtrip_' . substr(md5(uniqid()), 0, 8), $server->id),
            'remote' => '%',
        ]);

        $this->path = tempnam(sys_get_temp_dir(), 'roundtrip-test');
    }

    public function tearDown(): void
    {
        if ($this->database) {
            $this->app->make(DatabaseManagementService::class)->delete($this->database);
        }

        if ($this->path) {
            @unlink($this->path);
        }

        parent::tearDown();
    }

    public function testDatabaseIsRestoredExactly(): void
    {
        $pdo = $this->connect();
        $definer = sprintf('`%s`@`%%`', $this->database->username);

        $pdo->exec(<<<'SQL'
CREATE TABLE players (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(32) NOT NULL,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    flags BIT(8) NULL,
    skin BLOB NULL,
    note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    name_upper VARCHAR(32) AS (UPPER(name)) VIRTUAL,
    `odd``name` VARCHAR(64) NULL COMMENT 'it''s; "quoted" \\ comment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL);
        $pdo->exec('CREATE TABLE ranks (id INT PRIMARY KEY, player_id INT UNSIGNED NOT NULL, CONSTRAINT fk_player FOREIGN KEY (player_id) REFERENCES players (id) ON DELETE CASCADE) ENGINE=InnoDB');

        $insert = $pdo->prepare('INSERT INTO players (name, balance, flags, skin, note, `odd``name`) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute(['Steve', '100.50', 161, "\x00\xff\x10'\\\";\r\n\x1a", "Line1\nLine2; DROP TABLE x; -- no\r\n\\ ' \" 🔥", 'a;b']);
        $insert->execute(['Алекс', '0', null, null, null, null]);
        $insert->execute(['Bob', '3.14', 0, '', '', '/* c */ # d']);
        for ($i = 1; $i <= 1500; ++$i) {
            $pdo->exec("INSERT INTO ranks VALUES ($i, 1)");
        }

        $pdo->exec("CREATE DEFINER=$definer VIEW rich AS SELECT name FROM players WHERE balance > 10");
        $pdo->exec("CREATE DEFINER=$definer TRIGGER players_bi BEFORE INSERT ON players FOR EACH ROW BEGIN IF NEW.balance < 0 THEN SET NEW.balance = 0; END IF; SET NEW.note = CONCAT(IFNULL(NEW.note, ''), ';;'); END");
        $pdo->exec("CREATE DEFINER=$definer FUNCTION greet(n VARCHAR(32)) RETURNS VARCHAR(64) DETERMINISTIC RETURN CONCAT('Hi; ', n, '$$')");
        $pdo->exec("CREATE DEFINER=$definer PROCEDURE add_money(IN p INT, IN amount DECIMAL(12,2)) BEGIN UPDATE players SET balance = balance + amount WHERE id = p; END");

        $before = $this->snapshot($pdo);

        $this->app->make(DatabaseDumper::class)->dump($this->database, $this->path);
        $this->assertGreaterThan(0, filesize($this->path));

        $pdo->exec('DELETE FROM ranks WHERE id > 10');
        $pdo->exec("UPDATE players SET name = 'changed'");
        $pdo->exec('DROP VIEW rich');
        $pdo->exec('DROP FUNCTION greet');
        $pdo->exec('CREATE TABLE created_after_backup (id INT)');

        $this->app->make(DatabaseRestorer::class)->restore($this->database, $this->path);

        $this->assertEquals($before, $this->snapshot($pdo));
        $this->assertSame('Hi; x$$', $pdo->query("SELECT greet('x')")->fetchColumn());
    }

    private function snapshot(\PDO $pdo): array
    {
        $schema = $this->database->database;
        $snapshot = [];

        $tables = $pdo->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");
        $tables->execute([$schema]);
        foreach ($tables->fetchAll(\PDO::FETCH_COLUMN) as $table) {
            $snapshot["checksum:$table"] = $pdo->query("CHECKSUM TABLE `$table`")->fetch(\PDO::FETCH_NUM)[1];
            $snapshot["create:$table"] = preg_replace('/ AUTO_INCREMENT=\d+/', '', $pdo->query("SHOW CREATE TABLE `$table`")->fetch(\PDO::FETCH_NUM)[1]);
        }

        foreach ([
            'views' => 'SELECT TABLE_NAME, DEFINER, VIEW_DEFINITION FROM information_schema.VIEWS WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            'triggers' => 'SELECT TRIGGER_NAME, DEFINER, ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ? ORDER BY TRIGGER_NAME',
            'routines' => 'SELECT ROUTINE_NAME, ROUTINE_TYPE, DEFINER, ROUTINE_DEFINITION FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ? ORDER BY ROUTINE_NAME',
        ] as $key => $sql) {
            $statement = $pdo->prepare($sql);
            $statement->execute([$schema]);
            $snapshot[$key] = $statement->fetchAll(\PDO::FETCH_NUM);
        }

        return $snapshot;
    }

    private function connect(): \PDO
    {
        $connection = config('database.connections.mysql');

        return new \PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $connection['host'], $connection['port'], $this->database->database),
            $connection['username'],
            $connection['password'] ?? '',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }
}

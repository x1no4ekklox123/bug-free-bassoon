<?php

namespace Pterodactyl\Tests\Unit\Services\Databases\Backups;

use Pterodactyl\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Services\Databases\Backups\SqlStatementGuard;

class SqlStatementGuardTest extends TestCase
{
    private SqlStatementGuard $guard;

    public function setUp(): void
    {
        parent::setUp();

        $this->guard = new SqlStatementGuard('`u1_abc`@`%`', 's1_test');
    }

    #[DataProvider('allowedStatementProvider')]
    public function testAllowedStatementsAreReturnedUnchanged(string $statement): void
    {
        $this->assertSame($statement, $this->guard->secure($statement));
    }

    public static function allowedStatementProvider(): array
    {
        return [
            ['SET NAMES utf8mb4'],
            ["SET time_zone = '+00:00'"],
            ['SET FOREIGN_KEY_CHECKS = 0'],
            ["SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'"],
            ['DROP TABLE IF EXISTS `players`'],
            ['DROP PROCEDURE IF EXISTS `p``1`'],
            ["CREATE TABLE `players` (\n  `id` int NOT NULL\n) ENGINE=InnoDB"],
            ["INSERT INTO `players` (`id`, `name`) VALUES (1,'a'),(2,'b')"],
        ];
    }

    #[DataProvider('rejectedStatementProvider')]
    public function testDangerousStatementsAreRejected(string $statement): void
    {
        $this->expectException(DisplayException::class);

        $this->guard->secure($statement);
    }

    public static function rejectedStatementProvider(): array
    {
        return [
            ['USE `mysql`'],
            ["GRANT ALL PRIVILEGES ON *.* TO 'u1_abc'@'%'"],
            ["CREATE USER 'x'@'%' IDENTIFIED BY 'y'"],
            ['DROP DATABASE `s2_other`'],
            ['SET GLOBAL general_log = 1'],
            ["SET SESSION sql_mode = 'NO_BACKSLASH_ESCAPES'"],
            ["SET sql_mode = 'ANSI'"],
            ["INSERT INTO `mysql`.`user` (`Host`) VALUES ('%')"],
            ['INSERT INTO `players` (`id`) SELECT `id` FROM `mysql`.`user`'],
            ['CREATE TABLE `x` AS SELECT 1'],
            ['CREATE TRIGGER `s2_other`.`t` BEFORE INSERT ON `x` FOR EACH ROW SET @a = 1'],
            ['SELECT * FROM `players`'],
        ];
    }

    public function testDefinerIsForcedOnViews(): void
    {
        $this->assertSame(
            'CREATE ALGORITHM=UNDEFINED DEFINER=`u1_abc`@`%` SQL SECURITY DEFINER VIEW `v` AS select 1 AS `a`',
            $this->guard->secure('CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v` AS select 1 AS `a`')
        );

        $this->assertSame(
            'CREATE DEFINER=`u1_abc`@`%` VIEW `v` AS select 1 AS `a`',
            $this->guard->secure('CREATE VIEW `v` AS select 1 AS `a`')
        );
    }

    public function testDefinerIsForcedOnStoredPrograms(): void
    {
        $this->assertSame(
            'CREATE DEFINER=`u1_abc`@`%` TRIGGER players_bi BEFORE INSERT ON players FOR EACH ROW SET NEW.a = 1',
            $this->guard->secure("CREATE DEFINER='root'@'localhost' TRIGGER players_bi BEFORE INSERT ON players FOR EACH ROW SET NEW.a = 1")
        );

        $this->assertSame(
            'CREATE DEFINER=`u1_abc`@`%` PROCEDURE `s1_test`.`p`() BEGIN SELECT 1; END',
            $this->guard->secure('CREATE DEFINER=CURRENT_USER PROCEDURE `s1_test`.`p`() BEGIN SELECT 1; END')
        );

        $this->assertSame(
            'CREATE DEFINER=`u1_abc`@`%` FUNCTION `f`() RETURNS int DETERMINISTIC RETURN 1',
            $this->guard->secure('CREATE FUNCTION `f`() RETURNS int DETERMINISTIC RETURN 1')
        );
    }

    public function testDefinerCanBeStripped(): void
    {
        $this->assertSame(
            'CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v` AS select 1',
            SqlStatementGuard::stripDefiner('CREATE ALGORITHM=UNDEFINED DEFINER=`u1_abc`@`%` SQL SECURITY DEFINER VIEW `v` AS select 1')
        );

        $this->assertSame(
            'CREATE TRIGGER `t` BEFORE INSERT ON `x` FOR EACH ROW SET @a = 1',
            SqlStatementGuard::stripDefiner('CREATE DEFINER=`u1_abc`@`%` TRIGGER `t` BEFORE INSERT ON `x` FOR EACH ROW SET @a = 1')
        );
    }
}

<?php

namespace Pterodactyl\Tests\Unit\Services\Databases\Backups;

use Pterodactyl\Tests\TestCase;
use Pterodactyl\Services\Databases\Backups\SqlStatementReader;

class SqlStatementReaderTest extends TestCase
{
    public function testStatementsAreSplitOnDelimiters(): void
    {
        $sql = <<<'SQL'
-- leading comment; with a delimiter
SET NAMES utf8mb4;
CREATE TABLE `a;b` (
  `id` int NOT NULL COMMENT 'it''s; here',
  `x``y` varchar(10) DEFAULT "q\";"
);
/* block; comment */ INSERT INTO `a;b` (`id`) VALUES (1),(2); # trailing; comment
INSERT INTO `t` (`v`) VALUES ('a\';b'),('-- not a comment'),('/* nor */ this');
SQL;

        $this->assertSame([
            'SET NAMES utf8mb4',
            "CREATE TABLE `a;b` (\n  `id` int NOT NULL COMMENT 'it''s; here',\n  `x``y` varchar(10) DEFAULT \"q\\\";\"\n)",
            'INSERT INTO `a;b` (`id`) VALUES (1),(2)',
            "INSERT INTO `t` (`v`) VALUES ('a\\';b'),('-- not a comment'),('/* nor */ this')",
        ], $this->read($sql));
    }

    public function testDelimiterCommandIsSupported(): void
    {
        $sql = <<<'SQL'
DROP TRIGGER IF EXISTS `t`;
DELIMITER $$
CREATE TRIGGER `t` BEFORE INSERT ON `x` FOR EACH ROW BEGIN
  SET NEW.a = ';;';
  SET NEW.b = 1;
END $$
DELIMITER ;
SELECT 1;
SQL;

        $this->assertSame([
            'DROP TRIGGER IF EXISTS `t`',
            "CREATE TRIGGER `t` BEFORE INSERT ON `x` FOR EACH ROW BEGIN\n  SET NEW.a = ';;';\n  SET NEW.b = 1;\nEND",
            'SELECT 1',
        ], $this->read($sql));
    }

    public function testExecutableCommentsAreKept(): void
    {
        $this->assertSame(['/*!40101 SET NAMES utf8mb4 */'], $this->read("/*!40101 SET NAMES utf8mb4 */;\n"));
    }

    public function testEscapesSplitAcrossChunksAreHandled(): void
    {
        $chunks = ["INSERT INTO `t` (`v`) VALUES ('abc\\", "';def');", "\nSELECT 2;"];

        $this->assertSame(["INSERT INTO `t` (`v`) VALUES ('abc\\';def')", 'SELECT 2'], iterator_to_array((new SqlStatementReader())->statements($chunks), false));
    }

    public function testGzipFilesCanBeRead(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'reader-test');
        file_put_contents($path, gzencode("SET UNIQUE_CHECKS = 0;\nSET FOREIGN_KEY_CHECKS = 1;\n"));

        try {
            $this->assertSame(['SET UNIQUE_CHECKS = 0', 'SET FOREIGN_KEY_CHECKS = 1'], iterator_to_array(SqlStatementReader::fromGzipFile($path), false));
        } finally {
            unlink($path);
        }
    }

    private function read(string $sql): array
    {
        $lines = preg_split('/(?<=\n)/', $sql);

        return iterator_to_array((new SqlStatementReader())->statements($lines), false);
    }
}

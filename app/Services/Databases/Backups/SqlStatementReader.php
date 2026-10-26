<?php

namespace Pterodactyl\Services\Databases\Backups;

/**
 * Splits a SQL dump into individual statements. Understands quoted strings and
 * identifiers, comments and the mysql client's DELIMITER command, and works in a
 * streaming fashion so large dumps never have to be loaded into memory.
 */
class SqlStatementReader
{
    private const NORMAL = 0;
    private const QUOTED = 1;
    private const BLOCK_COMMENT = 2;

    /**
     * @return \Generator<string>
     */
    public static function fromGzipFile(string $path): \Generator
    {
        $file = gzopen($path, 'rb');
        if ($file === false) {
            throw new \RuntimeException('Unable to open the backup file for reading.');
        }

        $lines = (function () use ($file) {
            try {
                while (($line = gzgets($file, 4 * 1024 * 1024)) !== false) {
                    yield $line;
                }
            } finally {
                gzclose($file);
            }
        })();

        yield from (new self())->statements($lines);
    }

    /**
     * @param iterable<string> $lines chunks of the dump, ideally split on new lines
     *
     * @return \Generator<string>
     */
    public function statements(iterable $lines): \Generator
    {
        $delimiter = ';';
        $buffer = '';
        $content = false;
        $state = self::NORMAL;
        $quote = '';
        // Set when a chunk ended with a backslash inside of a quoted string.
        $escaped = false;

        foreach ($lines as $line) {
            if ($state === self::NORMAL && !$content && preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $line, $matches)) {
                $delimiter = $matches[1];
                $buffer = '';
                continue;
            }

            $length = strlen($line);
            $i = 0;

            if ($escaped && $length > 0) {
                $buffer .= $line[0];
                $escaped = false;
                $i = 1;
            }

            while ($i < $length) {
                if ($state === self::QUOTED) {
                    $stops = $quote === '`' ? '`' : $quote . '\\';
                    $j = $i + strcspn($line, $stops, $i);
                    $buffer .= substr($line, $i, $j - $i);
                    if ($j >= $length) {
                        break;
                    }

                    if ($line[$j] === '\\') {
                        $buffer .= substr($line, $j, 2);
                        $escaped = $j + 1 >= $length;
                        $i = $j + 2;
                    } elseif (($line[$j + 1] ?? '') === $quote) {
                        $buffer .= $quote . $quote;
                        $i = $j + 2;
                    } else {
                        $buffer .= $quote;
                        $state = self::NORMAL;
                        $i = $j + 1;
                    }

                    continue;
                }

                if ($state === self::BLOCK_COMMENT) {
                    $end = strpos($line, '*/', $i);
                    if ($end === false) {
                        break;
                    }

                    $buffer .= ' ';
                    $state = self::NORMAL;
                    $i = $end + 2;

                    continue;
                }

                $j = $i + strcspn($line, "'\"`-#/" . $delimiter[0], $i);
                if ($j > $i) {
                    $chunk = substr($line, $i, $j - $i);
                    $buffer .= $chunk;
                    $content = $content || trim($chunk) !== '';
                }

                if ($j >= $length) {
                    break;
                }

                $char = $line[$j];

                if (substr_compare($line, $delimiter, $j, strlen($delimiter)) === 0) {
                    if ($content) {
                        yield trim($buffer);
                    }

                    $buffer = '';
                    $content = false;
                    $i = $j + strlen($delimiter);
                } elseif ($char === "'" || $char === '"' || $char === '`') {
                    $state = self::QUOTED;
                    $quote = $char;
                    $buffer .= $char;
                    $content = true;
                    $i = $j + 1;
                } elseif ($char === '#' || ($char === '-' && ($line[$j + 1] ?? '') === '-' && ($j + 2 >= $length || ctype_space($line[$j + 2]) || ctype_cntrl($line[$j + 2])))) {
                    // Line comments run until the end of the line.
                    $buffer .= "\n";
                    break;
                } elseif ($char === '/' && ($line[$j + 1] ?? '') === '*' && ($line[$j + 2] ?? '') !== '!') {
                    $state = self::BLOCK_COMMENT;
                    $i = $j + 2;
                } else {
                    $buffer .= $char;
                    $content = true;
                    $i = $j + 1;
                }
            }
        }

        if ($content && trim($buffer) !== '') {
            yield trim($buffer);
        }
    }
}

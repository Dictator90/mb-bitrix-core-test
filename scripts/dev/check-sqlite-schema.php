<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$p = dirname(__DIR__, 2) . '/tests/.runtime/schema-check/bitrix.sqlite';
@mkdir(dirname($p), 0777, true);
@unlink($p);

\MB\BitrixTest\Database\SqliteTestDatabase::ensureSchema($p);

$pdo = new PDO('sqlite:' . $p);
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo 'TABLES: ' . implode(', ', $tables) . PHP_EOL;
echo in_array('b_option', $tables, true) ? "b_option OK\n" : "b_option MISSING\n";

<?php

$sql = file_get_contents(dirname(__DIR__, 2) . '/resources/schema/sqlite-minimal.sql');
foreach (preg_split('/;\s*\n/', $sql) ?: [] as $i => $statement) {
    $trimmed = trim(preg_replace('/^--.*$/m', '', $statement) ?? $statement);
    echo "=== #{$i} len=" . strlen($trimmed) . " ===\n";
    echo substr($trimmed, 0, 80) . "\n\n";
}

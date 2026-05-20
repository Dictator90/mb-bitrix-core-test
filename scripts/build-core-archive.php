<?php

declare(strict_types=1);

/**
 * Build a bundled zip from a local @bitrix directory and update archives/manifest.json.
 *
 * Usage:
 *   php scripts/build-core-archive.php [--source=PATH] [--version=26.150.0]
 */

$packageRoot = dirname(__DIR__);
$options = getopt('', ['source:', 'version:']);

$source = $options['source'] ?? dirname($packageRoot) . DIRECTORY_SEPARATOR . 'bitrix';
$source = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $source), DIRECTORY_SEPARATOR);

if (! is_dir($source . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main')) {
    fwrite(STDERR, "Invalid Bitrix source: {$source}\n");
    exit(1);
}

require $packageRoot . '/vendor/autoload.php';

$versionFile = $source . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
    . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'general' . DIRECTORY_SEPARATOR . 'version.php';
include $versionFile;
$smVersion = defined('SM_VERSION') ? (string) constant('SM_VERSION') : null;
$smVersionDate = defined('SM_VERSION_DATE') ? (string) constant('SM_VERSION_DATE') : null;

$version = $options['version'] ?? $smVersion;
if ($version === null) {
    fwrite(STDERR, "Unable to detect SM_VERSION. Pass --version=\n");
    exit(1);
}

$staging = $packageRoot . DIRECTORY_SEPARATOR . '.cache' . DIRECTORY_SEPARATOR . 'build-' . $version;
$archivesDir = $packageRoot . DIRECTORY_SEPARATOR . 'archives';
$zipName = 'bitrix-' . $version . '.zip';
$zipPath = $archivesDir . DIRECTORY_SEPARATOR . $zipName;

if (is_dir($staging)) {
    MB\BitrixTest\Install\CoreFilter::removeDirectory($staging);
}
mkdir($staging, 0777, true);

MB\BitrixTest\Install\CoreFilter::copyFiltered($source, $staging . DIRECTORY_SEPARATOR . 'bitrix');
$bitrixRoot = $staging . DIRECTORY_SEPARATOR . 'bitrix';

file_put_contents(
    $bitrixRoot . DIRECTORY_SEPARATOR . '.core-test.json',
    json_encode([
        'version' => $version,
        'built_at' => gmdate('c'),
        'source_sm_version' => $smVersion,
        'source_sm_version_date' => $smVersionDate,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
);

if (! is_dir($archivesDir)) {
    mkdir($archivesDir, 0777, true);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Cannot create zip: {$zipPath}\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($bitrixRoot, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $relative = 'bitrix/' . substr($item->getPathname(), strlen($bitrixRoot) + 1);
    $relative = str_replace('\\', '/', $relative);
    if ($item->isDir()) {
        $zip->addEmptyDir($relative);
    } else {
        $zip->addFile($item->getPathname(), $relative);
    }
}

$zip->close();
MB\BitrixTest\Install\CoreFilter::removeDirectory($staging);

$sha256 = hash_file('sha256', $zipPath);
$manifestPath = $archivesDir . DIRECTORY_SEPARATOR . 'manifest.json';
$manifest = is_file($manifestPath)
    ? json_decode((string) file_get_contents($manifestPath), true)
    : ['default' => null, 'releases' => []];

if (! is_array($manifest)) {
    $manifest = ['default' => null, 'releases' => []];
}

$releases = [];
$found = false;
foreach ($manifest['releases'] ?? [] as $release) {
    if (! is_array($release)) {
        continue;
    }
    if (($release['version'] ?? null) === $version) {
        $release = [
            'version' => $version,
            'archive' => $zipName,
            'sha256' => $sha256,
            'sm_version' => $smVersion ?? $version,
            'sm_version_date' => $smVersionDate,
        ];
        $found = true;
    }
    $releases[] = $release;
}

if (! $found) {
    $releases[] = [
        'version' => $version,
        'archive' => $zipName,
        'sha256' => $sha256,
        'sm_version' => $smVersion ?? $version,
        'sm_version_date' => $smVersionDate,
    ];
}

$manifest['default'] = $version;
$manifest['releases'] = $releases;

file_put_contents(
    $manifestPath,
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);

fwrite(STDOUT, "Built {$zipPath} (sha256: {$sha256})\n");

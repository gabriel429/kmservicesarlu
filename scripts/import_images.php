<?php
// Usage (CLI): php import_images.php /path/to/images.zip [--commit]
// By default runs as dry-run and prints mapping. Add --commit to perform moves.

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

if ($argc < 2) {
    echo "Usage: php scripts/import_images.php /path/to/archive.zip [--commit]\n";
    exit(1);
}

$zipPath = $argv[1];
$commit = in_array('--commit', $argv, true);

if (!file_exists($zipPath)) {
    fwrite(STDERR, "Archive not found: $zipPath\n");
    exit(2);
}

if (!class_exists('ZipArchive')) {
    fwrite(STDERR, "ZipArchive extension required.\n");
    exit(3);
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'km_import_' . time();
if (!mkdir($tmp, 0777, true) && !is_dir($tmp)) {
    fwrite(STDERR, "Unable to create temp dir: $tmp\n");
    exit(4);
}

$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) {
    fwrite(STDERR, "Cannot open zip file.\n");
    exit(5);
}

echo "Extracting archive to: $tmp\n";
$zip->extractTo($tmp);
$zip->close();

$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp));
foreach ($it as $file) {
    if ($file->isDir()) continue;
    $rel = substr($file->getPathname(), strlen($tmp) + 1);
    $files[] = $rel;
}

if (empty($files)) {
    echo "No files found in archive.\n";
    // cleanup
    // leave temp for inspection
    exit(0);
}

$destRoot = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
$mappings = [];

foreach ($files as $rel) {
    $basename = basename($rel);
    $lower = strtolower($rel);

    // heuristics: if path contains 'products/' or filename starts with 'product_' => products
    // if contains 'projects/' or filename starts with 'project_' => projects
    // else leave in root uploads
    $destSub = '';
    if (strpos($lower, 'products/') !== false || strpos($basename, 'product_') === 0) {
        $destSub = 'products';
    } elseif (strpos($lower, 'projects/') !== false || strpos($basename, 'project_') === 0) {
        $destSub = 'projects';
    }

    $destDir = $destRoot . ($destSub ? DIRECTORY_SEPARATOR . $destSub : '');
    if (!is_dir($destDir)) {
        if ($commit) {
            mkdir($destDir, 0777, true);
        }
    }

    $source = $tmp . DIRECTORY_SEPARATOR . $rel;
    $dest = $destDir . DIRECTORY_SEPARATOR . $basename;
    $mappings[] = ['source' => $source, 'dest' => $dest, 'rel' => $rel, 'destSub' => $destSub];
}

// Print summary
echo "Found " . count($mappings) . " files. Dry-run: " . ($commit ? 'no' : 'yes') . "\n\n";
foreach ($mappings as $m) {
    echo sprintf("%s -> %s\n", $m['rel'], substr($m['dest'], strlen(__DIR__) + 1));
}

if (!$commit) {
    echo "\nRun with --commit to perform the file moves.\n";
    // cleanup temp
    // leave temp for inspection
    exit(0);
}

// Perform moves
$moved = 0;
foreach ($mappings as $m) {
    $destDir = dirname($m['dest']);
    if (!is_dir($destDir)) mkdir($destDir, 0777, true);
    if (@copy($m['source'], $m['dest'])) {
        $moved++;
        // optional: preserve permissions
        @chmod($m['dest'], 0644);
    } else {
        fwrite(STDERR, "Failed to copy {$m['source']} -> {$m['dest']}\n");
    }
}

echo "\nMoved $moved files into $destRoot\n";

// cleanup temp
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) rmdir($file->getRealPath()); else unlink($file->getRealPath());
    }
    rmdir($dir);
}

rrmdir($tmp);

echo "Temp cleaned.\n";

exit(0);

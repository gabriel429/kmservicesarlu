<?php
// Simple image resizing with caching for service images.
// Uses GD if available; falls back to original image when not.

declare(strict_types=1);

// Config paths (use define instead of const to allow function calls)
define('ASSETS_DIR', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images'));
define('CACHE_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'images');
// Fallback cache dir (for platforms where project dir not writable)
$TMP_CACHE_DIR = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'km_cache_images';

// Ensure cache directory exists
if (!is_dir(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0775, true);
}
if (!is_dir($TMP_CACHE_DIR)) {
    @mkdir($TMP_CACHE_DIR, 0775, true);
}

// Helpers
function clampInt(int $value, int $min, int $max): int {
    return max($min, min($max, $value));
}

function getParam(string $key, string|int|null $default = null): ?string {
    if (!isset($_GET[$key])) return $default !== null ? (string)$default : null;
    return (string)$_GET[$key];
}

function allowedExtension(string $ext): bool {
    $ext = strtolower($ext);
    return in_array($ext, ['jpg','jpeg','png','webp'], true);
}

function serveFile(string $path, string $mime): void {
    if (!is_file($path)) {
        // Si le fichier n'existe pas, on retourne une image vide 1x1 transparent
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=2592000');
        // Image PNG 1x1 transparent
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        return;
    }
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=2592000'); // 30 days
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
    readfile($path);
}

// Read query params
$file    = basename((string)getParam('file'));
$p       = getParam('p'); // optional relative path under public (uploads or assets/images)
$remote  = getParam('url'); // optional remote image URL (http/https)
$width   = (int)clampInt((int)getParam('w', 720), 50, 2000);
$height  = (int)clampInt((int)getParam('h', 260), 50, 2000);
$quality = (int)clampInt((int)getParam('q', 85), 50, 95);
// Output format: jpeg (default), webp, png
$formatParam = strtolower(getParam('format', 'jpeg'));
$outputFormat = in_array($formatParam, ['jpeg','webp','png'], true) ? $formatParam : 'jpeg';

// Detect encoder support and apply explicit fallback if needed
$supportsWebp = function_exists('imagewebp') && (function_exists('imagetypes') ? ((imagetypes() & IMG_WEBP) !== 0) : true);
if ($outputFormat === 'webp' && !$supportsWebp) {
    $outputFormat = 'jpeg';
}

// Si aucun paramètre d'image n'est fourni, retourner une image vide
if (empty($file) && empty($p) && empty($remote)) {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400');
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    exit;
}

if ($remote && preg_match('/^https?:\/\//i', $remote)) {
    // Remote image via URL
    $sourceId = $remote;
    // Fetch remote bytes
    $ctx = stream_context_create([
        'http' => ['timeout' => 8, 'header' => "User-Agent: KMServices-ImgProxy\r\n"],
        'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true]
    ]);
    $data = @file_get_contents($remote, false, $ctx);
    if ($data === false) {
        // Retourner une image vide si le téléchargement échoue
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        exit;
    }
    $info = @getimagesizefromstring($data);
    $mime = $info && isset($info['mime']) ? strtolower($info['mime']) : 'image/jpeg';
    $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
    // Build cache path (prefer tmp cache if project cache not writable)
    $cacheBaseName = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename(parse_url($remote, PHP_URL_PATH) ?: 'remote')); 
    $cacheKey = substr(sha1($remote), 0, 12);
    $cacheExt  = $outputFormat === 'png' ? 'png' : ($outputFormat === 'webp' ? 'webp' : 'jpg');
    $prefCacheDir = is_writable(dirname(CACHE_DIR)) && is_writable(CACHE_DIR) ? CACHE_DIR : $TMP_CACHE_DIR;
    $cachePath = $prefCacheDir . '/' . $width . 'x' . $height . '-q' . $quality . '-' . $cacheKey . '-' . $cacheBaseName . '.' . $cacheExt;
    if (is_file($cachePath)) {
        $mimeOut = $outputFormat === 'png' ? 'image/png' : ($outputFormat === 'webp' ? 'image/webp' : 'image/jpeg');
        serveFile($cachePath, $mimeOut);
        exit;
    }
    // Create resource from string
    $srcImg = @imagecreatefromstring($data);
    if (!$srcImg) {
        // Retourner une image vide si le décodage échoue
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        exit;
    }
} elseif ($p) {
    // Using relative path under public directory
    $rel = ltrim($p, '/\\');
    $srcPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $rel);
    $srcNorm = $srcPath ? str_replace('\\', '/', $srcPath) : false;
    $uploadsNorm = str_replace('\\', '/', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'uploads'));
    $assetsImagesNorm = ASSETS_DIR ? str_replace('\\', '/', ASSETS_DIR) : false;
    $allowed = ($srcNorm !== false) && (
        ($uploadsNorm && strpos($srcNorm, $uploadsNorm) === 0) ||
        ($assetsImagesNorm && strpos($srcNorm, $assetsImagesNorm) === 0)
    );
    $ext = strtolower(pathinfo($srcPath ?: '', PATHINFO_EXTENSION));
    if (!$allowed || !is_file($srcPath) || !allowedExtension($ext)) {
        // Si le fichier n'existe pas, retourner une image vide transparente
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        exit;
    }
} else {
    // Backward-compat: using assets/images with file
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!allowedExtension($ext)) {
        // Fallback: redirect to original asset if extension not allowed
        header('Location: assets/images/' . rawurlencode($file));
        exit;
    }
    $srcPath = realpath(ASSETS_DIR . DIRECTORY_SEPARATOR . $file);
    $srcNorm = $srcPath ? str_replace('\\', '/', $srcPath) : false;
    $assetsNorm = ASSETS_DIR ? str_replace('\\', '/', ASSETS_DIR) : false;
    if ($srcNorm === false || $assetsNorm === false || strpos($srcNorm, $assetsNorm) !== 0 || !is_file($srcPath)) {
        // Retourner une image vide transparent
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        exit;
    }
}

// Build cache filename
// Cache key uses path context to avoid collisions
if (!isset($sourceId)) { $sourceId = $p ? $p : ('assets/images/' . $file); }
$cacheBaseName = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($sourceId));
$cacheKey = substr(sha1($sourceId), 0, 12);
$cacheExt  = $outputFormat === 'png' ? 'png' : ($outputFormat === 'webp' ? 'webp' : 'jpg');
$prefCacheDir = is_writable(dirname(CACHE_DIR)) && is_writable(CACHE_DIR) ? CACHE_DIR : $TMP_CACHE_DIR;
$cachePath = $prefCacheDir . '/' . $width . 'x' . $height . '-q' . $quality . '-' . $cacheKey . '-' . $cacheBaseName . '.' . $cacheExt;

// Serve from cache if exists
if (is_file($cachePath)) {
    $mime = $outputFormat === 'png' ? 'image/png' : ($outputFormat === 'webp' ? 'image/webp' : 'image/jpeg');
    serveFile($cachePath, $mime);
    exit;
}

// Resize using GD if available
if (!function_exists('imagecreatetruecolor')) {
    // GD not available; fallback to original
    header('Location: assets/images/' . rawurlencode($file));
    exit;
}

// Create image resource from source
if (!isset($srcImg)) {
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $srcImg = @imagecreatefromjpeg($srcPath);
            break;
        case 'png':
            $srcImg = @imagecreatefrompng($srcPath);
            break;
        case 'webp':
            if (function_exists('imagecreatefromwebp')) {
                $srcImg = @imagecreatefromwebp($srcPath);
            } else {
                $srcImg = @imagecreatefromjpeg($srcPath); // best-effort fallback
            }
            break;
        default:
            $srcImg = @imagecreatefromjpeg($srcPath);
    }
    if (!$srcImg) {
        // Cannot decode source; fallback
        header('Location: assets/images/' . rawurlencode($file));
        exit;
    }
}

$srcW = imagesx($srcImg);
$srcH = imagesy($srcImg);

// Compute cover crop: scale so that destination fully covered, then center-crop
$scale = max($width / $srcW, $height / $srcH);
$cropW = (int)round($width / $scale);
$cropH = (int)round($height / $scale);
$cropX = (int)max(0, floor(($srcW - $cropW) / 2));
$cropY = (int)max(0, floor(($srcH - $cropH) / 2));

$dstImg = imagecreatetruecolor($width, $height);
// For better quality
imageinterlace($dstImg, true);
imagecopyresampled(
    $dstImg, $srcImg,
    0, 0,                // dest x,y
    $cropX, $cropY,      // src x,y
    $width, $height,     // dest w,h
    $cropW, $cropH       // src w,h
);

// Save to cache in requested format
if ($outputFormat === 'png') {
    // Convert quality (0-9) approx from 50-95
    $pngQuality = max(0, min(9, (int)round((100 - $quality) / 10)));
    @imagepng($dstImg, $cachePath, $pngQuality);
} elseif ($outputFormat === 'webp' && function_exists('imagewebp')) {
    @imagewebp($dstImg, $cachePath, $quality);
} else {
    @imagejpeg($dstImg, $cachePath, $quality);
}

// Free resources
imagedestroy($dstImg);
imagedestroy($srcImg);

// Serve cached result (or fallback if write failed)
if (is_file($cachePath)) {
    $mime = $outputFormat === 'png' ? 'image/png' : ($outputFormat === 'webp' ? 'image/webp' : 'image/jpeg');
    serveFile($cachePath, $mime);
} else {
    header('Location: assets/images/' . rawurlencode($file));
}

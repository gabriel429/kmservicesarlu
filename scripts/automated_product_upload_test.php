<?php
// Script CLI pour simuler un upload de produit et vérifier fichier + DB
chdir(__DIR__ . '/..');
// PHP 7.4 compatibility: polyfill for str_contains si nécessaire
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return $needle === '' ? true : strpos($haystack, $needle) !== false;
    }
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/MySQL.php';

header_remove();
// Préparer dossier uploads
$uploadsDir = __DIR__ . '/../public/uploads/products/';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
}

$source = __DIR__ . '/../public/uploads/products/product_sample_1.png';
if (!file_exists($source)) {
    // créer une image PNG minimaliste
    $im = imagecreatetruecolor(200, 120);
    imagesavealpha($im, true);
    $trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $trans_colour);
    $col = imagecolorallocate($im, 50, 120, 180);
    imagestring($im, 5, 10, 40, 'sample', $col);
    imagepng($im, $source);
    imagedestroy($im);
}

$result = ['success' => false];
try {
    // Insérer un produit de test
    $nom = 'Automated Test Product ' . time();
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nom), '-')) . '-' . time();
    $ok = MySQLCore::execute(
        "INSERT INTO products (nom, slug, description, prix, stock, actif) VALUES (?, ?, ?, ?, ?, ?)",
        [$nom, $slug, 'Produit créé par test automatique', 9.99, 5, 1]
    );
    if (!$ok) throw new Exception('Impossible d\'insérer le produit');
    $productId = MySQLCore::lastInsertId();

    // Copier l'image sample vers uploads avec nom attendu
    $filename = 'product_' . $productId . '_' . time() . '.png';
    $target = $uploadsDir . $filename;
    if (!@copy($source, $target)) {
        throw new Exception('Échec copie fichier sample vers ' . $target);
    }

    // Mettre à jour la colonne image_principale
    MySQLCore::execute("UPDATE products SET image_principale = ? WHERE id = ?", [$filename, $productId]);

    // Insérer en galerie
    MySQLCore::execute("INSERT INTO product_images (product_id, image_path, ordre) VALUES (?, ?, ?)", [$productId, $filename, 0]);

    $result = [
        'success' => true,
        'product_id' => $productId,
        'filename' => $filename,
        'file_path' => realpath($target),
        'file_exists' => file_exists($target),
        'db_image_value' => MySQLCore::fetch("SELECT image_principale FROM products WHERE id = ?", [$productId])['image_principale'] ?? null
    ];

} catch (Throwable $e) {
    $result = ['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

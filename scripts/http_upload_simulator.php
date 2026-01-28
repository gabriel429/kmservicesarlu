<?php
// Simulate an admin product creation via HTTP multipart POST to the handler
$url = $argv[1] ?? 'http://localhost/kmservices/public/handlers/crud_products.php';
$sample = __DIR__ . '/../public/uploads/products/product_sample_1.png';
if (!file_exists($sample)) {
    // create a tiny sample image
    $im = imagecreatetruecolor(200,120);
    imagesavealpha($im, true);
    $trans = imagecolorallocatealpha($im,0,0,0,127);
    imagefill($im,0,0,$trans);
    $col = imagecolorallocate($im,80,140,200);
    imagestring($im,5,10,40,'http-test',$col);
    imagepng($im,$sample);
    imagedestroy($im);
}

$cfile = new CURLFile($sample, 'image/png', basename($sample));
$post = [
    'action' => 'create',
    'nom' => 'HTTP Test Product ' . time(),
    'description' => 'Créé par http_upload_simulator',
    'prix' => '14.50',
    'stock' => '3',
    'actif' => '1',
    'image' => $cfile
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$resp = curl_exec($ch);
if ($resp === false) {
    $err = curl_error($ch);
    echo json_encode(['success' => false, 'error' => $err], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(1);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode(['success' => true, 'http_code' => $httpCode, 'response' => $resp], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

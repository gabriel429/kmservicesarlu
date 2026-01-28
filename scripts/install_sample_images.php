<?php
// Crée des images d'exemple dans public/uploads/products et public/uploads/projects
error_reporting(E_ALL);
ini_set('display_errors', 1);

$base = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
$prodDir = $base . DIRECTORY_SEPARATOR . 'products';
$projDir = $base . DIRECTORY_SEPARATOR . 'projects';

@mkdir($prodDir, 0777, true);
@mkdir($projDir, 0777, true);

$png1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAHklEQVQoU2NkYGD4z8DAwMgABYwMDAwMDAwAAAO6AB2kPZxEAAAAAElFTkSuQmCC'); // 10x10 tiny
$png2 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAIElEQVQoU2NkYGD4z8DAwMgABYwMDAwMDAwMAgAEGgAABr8A1zVnW7sAAAAASUVORK5CYII=');

file_put_contents($prodDir . DIRECTORY_SEPARATOR . 'product_sample_1.png', $png1);
file_put_contents($prodDir . DIRECTORY_SEPARATOR . 'product_sample_2.png', $png2);
file_put_contents($projDir . DIRECTORY_SEPARATOR . 'project_sample_1.png', $png1);

echo "Sample images installed:\n";
echo " - " . str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $prodDir) . "\\product_sample_1.png\n";
echo " - " . str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $prodDir) . "\\product_sample_2.png\n";
echo " - " . str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $projDir) . "\\project_sample_1.png\n";

exit(0);

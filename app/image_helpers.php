<?php
/**
 * Helpers pour afficher les images de manière robuste
 */

/**
 * Retourne le tag <img> correct pour une image
 * Gère: URLs Supabase, chemins locaux, images manquantes
 */
function renderImage($imagePath, $alt = '', $width = '', $height = '', $classes = '', $loading = 'lazy') {
    if (empty($imagePath)) {
        // Pas d'image, retourner placeholder
        return '<img src="' . ASSET_URL . 'assets/images/placeholder_product.svg" alt="' . htmlspecialchars($alt) . '" ' . 
               ($classes ? 'class="' . htmlspecialchars($classes) . '"' : '') . '>';
    }
    
    $isUrl = is_string($imagePath) && str_starts_with($imagePath, 'http');
    $alt = htmlspecialchars($alt);
    $classes = htmlspecialchars($classes);
    
    if ($isUrl) {
        // C'est une URL Supabase
        $url = urlencode($imagePath);
        $srcW = $width ?: 400;
        $srcH = $height ?: 200;
        
        return '<img src="/img?url=' . $url . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp" ' .
               'alt="' . $alt . '" loading="' . $loading . '" ' .
               ($width ? 'width="' . $width . '" ' : '') .
               ($height ? 'height="' . $height . '" ' : '') .
               ($classes ? 'class="' . $classes . '" ' : '') .
               'srcset="/img?url=' . $url . '&w=' . ($width ? intval($width)/2 : 200) . '&h=' . ($height ? intval($height)/2 : 100) . '&q=85&format=webp ' . 
               intval($width ? $width/2 : 200) . 'w, ' .
               '/img?url=' . $url . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp ' . $srcW . 'w" ' .
               'sizes="(max-width: 768px) 100vw, ' . $srcW . 'px">';
    } else {
        // Chemin local
        $srcPath = 'uploads/' . htmlspecialchars($imagePath);
        $srcW = $width ?: 400;
        $srcH = $height ?: 200;
        
        return '<img src="/img?p=' . $srcPath . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp" ' .
               'alt="' . $alt . '" loading="' . $loading . '" ' .
               ($width ? 'width="' . $width . '" ' : '') .
               ($height ? 'height="' . $height . '" ' : '') .
               ($classes ? 'class="' . $classes . '" ' : '') .
               'srcset="/img?p=' . $srcPath . '&w=' . ($width ? intval($width)/2 : 200) . '&h=' . ($height ? intval($height)/2 : 100) . '&q=85&format=webp ' . 
               intval($width ? $width/2 : 200) . 'w, ' .
               '/img?p=' . $srcPath . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp ' . $srcW . 'w" ' .
               'sizes="(max-width: 768px) 100vw, ' . $srcW . 'px">';
    }
}

?>

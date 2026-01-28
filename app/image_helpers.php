<?php
/**
 * Helpers pour afficher les images de manière robuste
 */

/**
 * Retourne le tag <img> correct pour une image
 * Gère: URLs Supabase, chemins locaux, images manquantes
 */
function renderImage($imagePath, $alt = '', $width = '', $height = '', $classes = '', $loading = 'lazy') {
    // Normaliser l'entrée: accepter JSON array, chemins absolus, préfixes variés
    if (empty($imagePath)) {
        return '<img src="' . ASSET_URL . 'assets/images/placeholder_product.svg" alt="' . htmlspecialchars($alt) . '" ' .
               ($classes ? 'class="' . htmlspecialchars($classes) . '"' : '') . '>';
    }

    // Si imagePath est un JSON array stocké en DB (ex: ["a.jpg","b.jpg"]), prendre le premier
    if (is_string($imagePath) && (str_starts_with($imagePath, '[') || str_starts_with($imagePath, '{'))) {
        $decoded = json_decode($imagePath, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // si array indexé, prendre le premier élément
            $first = reset($decoded);
            if (!empty($first) && is_string($first)) {
                $imagePath = $first;
            }
        }
    }

    // Retirer éventuels préfixes inadaptés
    $imagePath = trim((string)$imagePath);
    // si commence par '/uploads' ou 'uploads/' ou 'public/uploads/' => enlever le préfixe
    $imagePath = preg_replace('#^(/?public/)?/?uploads/#i', '', $imagePath);
    // si c'est une URL complète
    $isUrl = is_string($imagePath) && preg_match('#^https?://#i', $imagePath);

    $alt = htmlspecialchars($alt);
    $classes = htmlspecialchars($classes);
    $srcW = $width ?: 400;
    $srcH = $height ?: 200;

    if ($isUrl) {
        $url = urlencode($imagePath);
        return '<img src="/img?url=' . $url . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp" alt="' . $alt . '" loading="' . $loading . '" ' .
               ($width ? 'width="' . $width . '" ' : '') . ($height ? 'height="' . $height . '" ' : '') . ($classes ? 'class="' . $classes . '" ' : '') .
               'srcset="/img?url=' . $url . '&w=' . intval($srcW/2) . '&h=' . intval($srcH/2) . '&q=85&format=webp ' . intval($srcW/2) . 'w, /img?url=' . $url . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp ' . $srcW . 'w" sizes="(max-width: 768px) 100vw, ' . $srcW . 'px">';
    }

    // Valeur locale : imagePath est maintenant le nom relatif sous uploads/
    $safe = htmlspecialchars($imagePath);
    $srcPath = 'uploads/' . $safe;

    return '<img src="/img?p=' . $srcPath . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp" alt="' . $alt . '" loading="' . $loading . '" ' .
           ($width ? 'width="' . $width . '" ' : '') . ($height ? 'height="' . $height . '" ' : '') . ($classes ? 'class="' . $classes . '" ' : '') .
           'srcset="/img?p=' . $srcPath . '&w=' . intval($srcW/2) . '&h=' . intval($srcH/2) . '&q=85&format=webp ' . intval($srcW/2) . 'w, /img?p=' . $srcPath . '&w=' . $srcW . '&h=' . $srcH . '&q=85&format=webp ' . $srcW . 'w" sizes="(max-width: 768px) 100vw, ' . $srcW . 'px">';
}

?>

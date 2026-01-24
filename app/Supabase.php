<?php
/**
 * Supabase Storage client minimal pour uploads et URLs publiques
 */

namespace App;

class SupabaseStorage {
    private $baseUrl;
    private $apiKey;
    private $bucket;

    public function __construct($baseUrl, $apiKey, $bucket = 'uploads') {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->bucket = $bucket ?: 'uploads';
    }

    public static function fromEnv($bucket = null) {
        // Lire constantes si définies
        if (!defined('SUPABASE_URL') || !defined('SUPABASE_SERVICE_ROLE_KEY')) {
            return null;
        }
        $baseUrl = SUPABASE_URL;
        $apiKey = SUPABASE_SERVICE_ROLE_KEY;
        $bucketName = $bucket ?: (defined('SUPABASE_BUCKET') ? SUPABASE_BUCKET : 'uploads');
        if (!$baseUrl || !$apiKey) return null;
        return new self($baseUrl, $apiKey, $bucketName);
    }

    /**
     * Upload d'un fichier binaire vers Supabase Storage.
     * Retourne l'URL publique si le bucket est public, sinon le chemin.
     */
    public function uploadFile($tmpPath, $destPath, $contentType = 'application/octet-stream', $upsert = true) {
        if (!is_readable($tmpPath)) {
            return null;
        }
        $url = $this->baseUrl . '/storage/v1/object/' . rawurlencode($this->bucket) . '/' . str_replace('%2F', '/', rawurlencode($destPath));

        $ch = curl_init($url);
        $data = file_get_contents($tmpPath);
        if ($data === false) return null;

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: ' . ($contentType ?: 'application/octet-stream'),
            'x-upsert: ' . ($upsert ? 'true' : 'false'),
        ];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return null;
        }
        if ($httpCode >= 200 && $httpCode < 300) {
            // Construire l'URL publique standard
            return $this->publicUrl($destPath);
        }
        return null;
    }

    /**
     * URL publique (bucket avec accès public activé)
     */
    public function publicUrl($destPath) {
        return $this->baseUrl . '/storage/v1/object/public/' . rawurlencode($this->bucket) . '/' . str_replace('%2F', '/', rawurlencode($destPath));
    }

    /**
     * Supprimer un objet par chemin (key) dans le bucket.
     */
    public function deleteObject($destPath) {
        $url = $this->baseUrl . '/storage/v1/object/' . rawurlencode($this->bucket) . '/' . str_replace('%2F', '/', rawurlencode($destPath));
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
        ];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Supprimer un objet à partir de son URL publique.
     */
    public function deleteByPublicUrl($publicUrl) {
        if (!is_string($publicUrl)) return false;
        $prefix = rtrim($this->baseUrl, '/') . '/storage/v1/object/public/';
        if (str_starts_with($publicUrl, $prefix)) {
            $rest = substr($publicUrl, strlen($prefix));
            // rest = bucket/path
            $parts = explode('/', $rest, 2);
            if (count($parts) === 2) {
                $bucket = $parts[0];
                $path = $parts[1];
                // Si l'URL pointe vers un autre bucket, mettre à jour temporairement
                $origBucket = $this->bucket;
                $this->bucket = $bucket;
                $ok = $this->deleteObject($path);
                $this->bucket = $origBucket;
                return $ok;
            }
        }
        return false;
    }
}

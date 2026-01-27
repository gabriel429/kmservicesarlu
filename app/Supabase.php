<?php

namespace App;

class Supabase {
    private static $projectUrl;
    private static $anonKey;
    
    public static function init() {
        self::$projectUrl = getenv('SUPABASE_URL') ?: $_ENV['SUPABASE_URL'] ?? '';
        self::$anonKey = getenv('SUPABASE_ANON_KEY') ?: $_ENV['SUPABASE_ANON_KEY'] ?? '';
    }
    
    public static function uploadFile($bucket, $path, $fileContent, $contentType = 'image/jpeg') {
        if (!self::$projectUrl || !self::$anonKey) {
            self::init();
        }
        
        if (!self::$projectUrl || !self::$anonKey) {
            throw new \Exception('Supabase credentials not configured');
        }
        
        $url = rtrim(self::$projectUrl, '/') . '/storage/v1/object/' . $bucket . '/' . ltrim($path, '/');
        
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Authorization: Bearer ' . self::$anonKey,
                    'Content-Type: ' . $contentType,
                ],
                'content' => $fileContent,
                'timeout' => 30,
            ],
            'ssl' => ['verify_peer' => true]
        ]);
        
        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            throw new \Exception('Failed to upload file to Supabase Storage');
        }
        
        return json_decode($response, true);
    }
    
    public static function deleteFile($bucket, $path) {
        if (!self::$projectUrl || !self::$anonKey) {
            self::init();
        }
        
        if (!self::$projectUrl || !self::$anonKey) {
            throw new \Exception('Supabase credentials not configured');
        }
        
        $url = rtrim(self::$projectUrl, '/') . '/storage/v1/object/' . $bucket . '/' . ltrim($path, '/');
        
        $ctx = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'header' => [
                    'Authorization: Bearer ' . self::$anonKey,
                ],
                'timeout' => 30,
            ],
            'ssl' => ['verify_peer' => true]
        ]);
        
        $response = @file_get_contents($url, false, $ctx);
        return $response !== false;
    }
    
    public static function getPublicUrl($bucket, $path) {
        if (!self::$projectUrl) {
            self::init();
        }
        
        if (!self::$projectUrl) {
            throw new \Exception('Supabase URL not configured');
        }
        
        return rtrim(self::$projectUrl, '/') . '/storage/v1/object/public/' . $bucket . '/' . ltrim($path, '/');
    }
}

Supabase::init();

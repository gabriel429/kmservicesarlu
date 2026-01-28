<?php
// Supabase integration removed — use local uploads instead.
// This file kept as a compatibility shim to avoid fatal includes; do not use.

namespace App;

class Supabase
{
    public static function __callStatic($name, $args)
    {
        throw new \Exception('Supabase has been removed. Use local uploads (public/uploads) instead.');
    }
}


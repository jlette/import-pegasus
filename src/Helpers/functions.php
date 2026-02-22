<?php

namespace App\Helpers;

class Helper
{
    public static function asset($path)
    {
        // On s'assure de ne pas avoir de double slash
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

// Fonction helper courte (accès facile)
if (!function_exists('asset')) {
    function asset($path) {
        return Helper::asset($path);
    }
}
<?php

namespace App\Support;

/**
 * Interrupteur global de l'audit (pour désactiver pendant les seeds/imports massifs).
 */
class Audit
{
    public static bool $enabled = true;

    public static function disable(): void { self::$enabled = false; }
    public static function enable(): void  { self::$enabled = true; }

    public static function withoutAuditing(callable $callback): mixed
    {
        $previous = self::$enabled;
        self::$enabled = false;
        try {
            return $callback();
        } finally {
            self::$enabled = $previous;
        }
    }
}

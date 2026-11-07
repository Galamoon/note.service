<?php

namespace Galamoon\Notes\Helpers;

class Sanitizer
{
    /**
     * Sanitize string for XSS protection.
     * Handles double-encoding protection for edit operations.
     */
    public static function sanitize(string $value): string
    {
        // Decode any existing HTML entities (handles double-encoding on edit)
        $value = htmlspecialchars_decode($value, ENT_QUOTES | ENT_HTML5);
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        // Strip HTML tags
        $value = strip_tags($value);
        // Re-encode with strict flags
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}

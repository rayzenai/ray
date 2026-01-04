<?php

use RayzenAI\Ray\Ray;

if (! function_exists('ray')) {
    /**
     * Log debug data to the Ray debug viewer.
     * View at: /debug/ray (local only)
     *
     * Usage:
     *   ray($data)                         // Quick debug with default title
     *   ray('label', $data)                // With custom label
     *   ray($data)->label('Users')->green() // Chainable with colors
     *   ray($data)->die()                  // Like dd() but with Ray
     */
    function ray(mixed $titleOrData = null, mixed $data = null): Ray
    {
        if (! function_exists('isLocal') || ! isLocal()) {
            return new Ray($data ?? $titleOrData);
        }

        if ($data === null && $titleOrData !== null) {
            return (new Ray($titleOrData))->label('debug');
        }

        if ($titleOrData !== null && $data !== null) {
            return (new Ray($data))->label((string) $titleOrData);
        }

        return new Ray;
    }
}

if (! function_exists('rayDie')) {
    /**
     * Log to Ray and die (like dd() but with Ray).
     */
    function rayDie(mixed $data, string $title = 'die'): never
    {
        ray($title, $data)->die();
    }
}

if (! function_exists('formatDebugData')) {
    /**
     * Format debug data with JSON syntax highlighting.
     */
    function formatDebugData(mixed $data): string
    {
        if (is_string($data)) {
            return e($data);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return e(print_r($data, true));
        }

        $highlighted = preg_replace_callback('/(\"(?:[^\"\\\\]|\\\\.)*\")\s*:/', fn ($m) => '<span class="json-key">' . e($m[1]) . '</span>:', $json);
        $highlighted = preg_replace_callback('/:\s*(\"(?:[^\"\\\\]|\\\\.)*\")/', fn ($m) => ': <span class="json-string">' . e($m[1]) . '</span>', $highlighted);
        $highlighted = preg_replace_callback('/:\s*(-?\d+\.?\d*)/', fn ($m) => ': <span class="json-number">' . $m[1] . '</span>', $highlighted);
        $highlighted = preg_replace_callback('/:\s*(true|false)/', fn ($m) => ': <span class="json-boolean">' . $m[1] . '</span>', $highlighted);
        $highlighted = preg_replace_callback('/:\s*(null)/', fn ($m) => ': <span class="json-null">' . $m[1] . '</span>', $highlighted);

        return $highlighted;
    }
}

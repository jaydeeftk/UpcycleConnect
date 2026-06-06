<?php

function t(string $key, string $fallback = ''): string
{
    static $translations = [];
    $lang = $_SESSION['lang'] ?? 'fr';
    if (!isset($translations[$lang])) {
        $file = __DIR__ . "/../../lang/{$lang}.php";
        $translations[$lang] = file_exists($file) ? require $file : [];
    }
    return $translations[$lang][$key] ?? ($fallback ?: $key);
}

function currentLang(): string
{
    return $_SESSION['lang'] ?? 'fr';
}

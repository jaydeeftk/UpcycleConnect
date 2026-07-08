<?php

if (!function_exists('uc_image')) {

    function uc_image(string $theme, $seed = ''): string
    {
        $themes = ['evenement', 'formation', 'service', 'prestation', 'objet', 'conseil'];
        if (!in_array($theme, $themes, true)) {
            $theme = 'service';
        }
        $variantes = 4;
        $idx = is_numeric($seed) ? (int) $seed : (int) crc32((string) $seed);
        $idx = abs($idx) % $variantes;
        return '/images/categories/' . $theme . '-' . $idx . '.svg';
    }
}

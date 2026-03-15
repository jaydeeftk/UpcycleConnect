<?php

return [
    'name' => 'UpcycleConnect',
    'env' => 'development',
    'debug' => true,
    'url' => 'http://localhost:8000',
    
    'api' => [
        'base_url' => 'http://localhost:8080/api',
        'timeout' => 30,
    ],
    
    'locales' => ['fr', 'en', 'de', 'es'],
    'locale' => 'fr',
    'fallback_locale' => 'fr',
    
    'timezone' => 'Europe/Paris',
];
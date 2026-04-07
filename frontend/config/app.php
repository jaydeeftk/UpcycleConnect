<?php

return [
    'name' => 'UpcycleConnect',
    'url' => getenv('APP_URL') ?: 'http://145.241.169.248', 
    
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => getenv('APP_DEBUG') === 'true',
    
    'api' => [
        'base_url' => getenv('API_BASE_URL') ?: 'http://api:8080/api',
        'timeout' => 30,
    ],
    
    'locales' => ['fr', 'en', 'de', 'es'],
    'locale' => 'fr',
    'fallback_locale' => 'fr',
    
    'timezone' => 'Europe/Paris',
];
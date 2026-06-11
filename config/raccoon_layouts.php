<?php

return [
    'route_prefix' => 'raccoon-layouts',
    'middleware' => ['web', 'auth'],
    'user_model' => App\Models\User::class,
    'page_key_strategy' => 'url', // 'url' | 'route_name'
    'locale' => 'en', // 'en' | 'it' | 'es' | 'fr' | 'de'
];

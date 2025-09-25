<?php

return [
    'paths' => ['api/*'], // agrega más si usas otros
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:4321', 'http://127.0.0.1:4321'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // como ya no usamos cookies/sesión, déjalo en false
];
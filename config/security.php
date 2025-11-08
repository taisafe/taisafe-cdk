<?php

return [
    'allowed_ips' => [
        '10.0.0.0/8',
        '192.168.0.0/16',
        '172.16.0.0/12',
    ],
    'headers' => [
        'Content-Security-Policy' => "default-src 'self'",
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
    ],
];

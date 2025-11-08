<?php
return [
    'db' => [
        'host' => 'db',
        'port' => 3306,
        'database' => 'taisafe_cdk',
        'username' => 'taisafe',
        'password' => 'ChangeMe123!',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'session' => [
        'name' => 'taisafe_session',
        'lifetime' => 1800,
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    'security' => [
        'allowed_internal_ips' => ['10.0.0.0/8', '192.168.0.0/16'],
        'csrf_token_name' => 'taisafe_csrf',
    ],
];

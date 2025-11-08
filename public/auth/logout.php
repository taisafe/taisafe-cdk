<?php

use Taisafe\Services\AuthService;

require_once __DIR__ . '/../../tests/bootstrap.php';

$auth = new AuthService();
$auth->logout();
header('Location: /auth/login.php');
exit;

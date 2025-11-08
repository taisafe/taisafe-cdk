<?php

use Taisafe\Middleware\RoleGuard;
use Taisafe\Middleware\SessionMiddleware;

require_once __DIR__ . '/../tests/bootstrap.php';

$session = new SessionMiddleware();
$guard = new RoleGuard($session);

try {
    $session->start();
    $guard->enforce(['admin', 'operator', 'auditor']);
} catch (Throwable $e) {
    header('Location: /auth/login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title>Taisafe-CDK Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">控制台</h1>
            <small class="text-muted">使用者 #<?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8') ?> · 角色 <?= htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8') ?></small>
        </div>
        <a class="btn btn-outline-secondary" href="/auth/logout.php">登出</a>
    </header>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">序號批次</h5>
                    <p class="card-text">建立批次並產出序號清單。</p>
                    <a href="/partials/batches/create_form.php" class="btn btn-primary">建立批次</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">序號核銷</h5>
                    <p class="card-text">快速輸入與核銷單筆序號。</p>
                    <a href="/partials/redeem/form.php" class="btn btn-primary">核銷序號</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

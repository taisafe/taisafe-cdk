<?php

use Taisafe\Middleware\SessionMiddleware;

require_once __DIR__ . '/../tests/bootstrap.php';

$session = new SessionMiddleware();
try {
    $session->start();
} catch (Throwable) {
    // Ignore expired session for landing page.
}

$role = $_SESSION['role'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title>Taisafe-CDK 控制台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <h1 class="mb-4">Taisafe-CDK 控制台</h1>
    <?php if ($userId): ?>
        <p class="text-success">已登入：使用者 #<?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8') ?>（角色：<?= htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8') ?>）</p>
        <div class="list-group mb-3">
            <a class="list-group-item list-group-item-action" href="/partials/batches/create_form.php">建立序號批次</a>
            <a class="list-group-item list-group-item-action" href="/partials/redeem/form.php">序號核銷</a>
            <a class="list-group-item list-group-item-action" href="/auth/logout.php">登出</a>
        </div>
    <?php else: ?>
        <p>請先登入以存取批次建立與核銷功能。</p>
        <a class="btn btn-primary" href="/auth/login.php">前往登入</a>
    <?php endif; ?>
</body>
</html>

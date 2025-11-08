<?php

use Taisafe\Services\AuthService;
use Taisafe\Middleware\SessionMiddleware;

require_once __DIR__ . '/../../tests/bootstrap.php';

$session = new SessionMiddleware();
try {
    $session->start();
} catch (Throwable $e) {
    // expired session is acceptable when hitting login page
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account = trim($_POST['account'] ?? '');
    $password = trim($_POST['password'] ?? '');
    try {
        $auth = new AuthService();
        $auth->login($account, $password, $_SERVER['REMOTE_ADDR'] ?? '');
        header('Location: /dashboard.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title>Taisafe-CDK Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
<main class="card shadow-sm p-4" style="min-width: 320px; max-width: 360px;">
    <h1 class="h4 mb-3 text-center">登入</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <form method="post" class="vstack gap-3">
        <div>
            <label class="form-label">帳號</label>
            <input type="text" name="account" class="form-control" required autofocus>
        </div>
        <div>
            <label class="form-label">密碼</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">登入</button>
    </form>
</main>
</body>
</html>

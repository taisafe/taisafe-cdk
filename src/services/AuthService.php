<?php

namespace Taisafe\Services;

use RuntimeException;
use Taisafe\Repositories\UserRepository;
use Taisafe\Services\AuditLogger;

class AuthService
{
    public function __construct(private UserRepository $users = new UserRepository())
    {
    }

    public function login(string $account, string $password, string $ip = ''): void
    {
        $user = $this->users->findByAccount($account);
        if (! $user || ! password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('INVALID_CREDENTIALS');
        }
        if ($user['status'] !== 'active') {
            throw new RuntimeException('ACCOUNT_DISABLED');
        }
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['ip'] = $ip;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $this->users->updateLastLogin((int) $user['id']);
        $logger = new AuditLogger();
        $logger->logAction([
            'actor_id' => (int) $user['id'],
            'action' => 'LOGIN',
            'target_type' => 'auth',
            'result_code' => 'LOGIN_SUCCESS',
            'ip' => $ip,
        ]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $logger = new AuditLogger();
            $logger->logAction([
                'actor_id' => $_SESSION['user_id'] ?? null,
                'action' => 'LOGOUT',
                'target_type' => 'auth',
                'result_code' => 'LOGOUT',
            ]);
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
        }
    }
}

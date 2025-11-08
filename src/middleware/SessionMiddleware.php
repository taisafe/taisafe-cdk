<?php

namespace Taisafe\Middleware;

use DateTimeImmutable;
use RuntimeException;
use Taisafe\Repositories\DatabaseConnection;

/**
 * Session validation middleware (T011) enforcing 30-minute expiry.
 */
class SessionMiddleware
{
    private int $timeoutSeconds = 1800;

    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('taisafe_session');
            session_set_cookie_params([
                'lifetime' => 0,
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
        $now = time();
        if (! isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = $now;
        }
        if (($now - (int) $_SESSION['last_activity']) > $this->timeoutSeconds) {
            $this->forceLogout();
            throw new RuntimeException('SESSION_EXPIRED');
        }
        $_SESSION['last_activity'] = $now;
    }

    public function requireRole(array $allowedRoles): void
    {
        if (! isset($_SESSION['role']) || ! in_array($_SESSION['role'], $allowedRoles, true)) {
            $this->logDenied($_SESSION['user_id'] ?? null, $allowedRoles);
            throw new RuntimeException('SEC_DENY_ROLE');
        }
    }

    private function forceLogout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    private function logDenied(?int $actorId, array $allowedRoles): void
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO audit_log (actor_id, action, target_type, payload, result_code) VALUES (:actor, :action, :target, :payload, :result)');
        $stmt->execute([
            ':actor' => $actorId,
            ':action' => 'SEC_DENY_ROLE',
            ':target' => 'middleware',
            ':payload' => json_encode(['required_roles' => $allowedRoles]),
            ':result' => 'SEC_DENY_ROLE',
        ]);
    }
}

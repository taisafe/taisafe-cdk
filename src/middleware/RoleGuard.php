<?php

namespace Taisafe\Middleware;

use RuntimeException;
use Taisafe\Repositories\DatabaseConnection;
use Taisafe\Services\AuditLogger;

class RoleGuard
{
    public function __construct(private SessionMiddleware $sessionMiddleware = new SessionMiddleware())
    {
    }

    public function enforce(array $roles): void
    {
        $this->sessionMiddleware->start();
        if (! isset($_SESSION['role']) || ! in_array($_SESSION['role'], $roles, true)) {
            $logger = new AuditLogger(DatabaseConnection::get());
            $logger->logAction([
                'actor_id' => $_SESSION['user_id'] ?? null,
                'action' => 'SEC_DENY_ROLE',
                'target_type' => 'access_control',
                'result_code' => 'SEC_DENY_ROLE',
            ]);
            throw new RuntimeException('SEC_DENY_ROLE');
        }
    }
}

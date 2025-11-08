<?php

namespace Taisafe\Services;

use PDO;
use Taisafe\Repositories\DatabaseConnection;

/**
 * Centralized audit logging utility (T010).
 */
class AuditLogger
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DatabaseConnection::get();
    }

    /**
     * Write audit log entry per FR-007 / EC requirements.
     *
     * @param array{actor_id?:int,action:string,target_type?:string,target_id?:string,payload?:array,result_code?:string,ip?:string} $data
     */
    public function logAction(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_log (actor_id, action, target_type, target_id, payload, ip, result_code)
             VALUES (:actor_id, :action, :target_type, :target_id, :payload, :ip, :result_code)'
        );
        $stmt->execute([
            ':actor_id' => $data['actor_id'] ?? null,
            ':action' => $data['action'],
            ':target_type' => $data['target_type'] ?? null,
            ':target_id' => $data['target_id'] ?? null,
            ':payload' => isset($data['payload']) ? json_encode($data['payload']) : null,
            ':ip' => isset($data['ip']) ? inet_pton($data['ip']) : null,
            ':result_code' => $data['result_code'] ?? null,
        ]);
    }
}

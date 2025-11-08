<?php

namespace Taisafe\Services;

use DateTimeImmutable;
use PDO;
use Throwable;
use Taisafe\Repositories\DatabaseConnection;
use Taisafe\Validators\RedeemValidator;

class RedeemService
{
    private PDO $pdo;
    private RedeemValidator $validator;
    private AuditLogger $logger;
    private string $driver;

    public function __construct(?PDO $pdo = null, ?RedeemValidator $validator = null, ?AuditLogger $logger = null)
    {
        $this->pdo = $pdo ?? DatabaseConnection::get();
        $this->validator = $validator ?? new RedeemValidator();
        $this->logger = $logger ?? new AuditLogger($this->pdo);
        $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * @param array{code?:string,channel?:string} $input
     * @return array{status:string,redeemed_at?:string,code?:string}
     */
    public function redeem(array $input, int $userId, string $ip = ''): array
    {
        $payload = $this->validator->validateInput($input);
        $now = new DateTimeImmutable('now');

        $this->pdo->beginTransaction();
        try {
            $record = $this->findCode($payload['code']);
            $state = $this->validator->determineState($record, $now);
            if ($state !== 'ready') {
                $this->pdo->rollBack();
                $this->logAttempt($state, $payload['code'], $userId, $ip, $payload['channel']);
                return ['status' => $state];
            }

            $redeemedAt = $now->format('Y-m-d H:i:s');
            $updated = $this->markRedeemed((int) $record['id'], $userId, $redeemedAt);
            if (! $updated) {
                $this->pdo->rollBack();
                $this->logAttempt('used', $payload['code'], $userId, $ip, $payload['channel']);
                return ['status' => 'used'];
            }

            $this->pdo->commit();
            $this->logAttempt('success', $payload['code'], $userId, $ip, $payload['channel']);
            return [
                'status' => 'success',
                'redeemed_at' => $redeemedAt,
                'code' => $payload['code'],
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return array{id:int,status:string,batch_id:int,batch_expires_at:?string}|null
     */
    private function findCode(string $code): ?array
    {
        $lock = $this->driver === 'mysql' ? ' FOR UPDATE' : '';
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.status, c.batch_id, b.expires_at AS batch_expires_at
             FROM cdk_codes c
             INNER JOIN cdk_batches b ON c.batch_id = b.id
             WHERE c.code = :code' . $lock
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function markRedeemed(int $codeId, int $userId, string $redeemedAt): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE cdk_codes
             SET status = :status, redeemed_by = :user_id, redeemed_at = :redeemed_at
             WHERE id = :id AND status = :expected'
        );
        $stmt->execute([
            ':status' => 'redeemed',
            ':user_id' => $userId,
            ':redeemed_at' => $redeemedAt,
            ':id' => $codeId,
            ':expected' => 'issued',
        ]);
        return $stmt->rowCount() > 0;
    }

    private function logAttempt(string $status, string $code, int $userId, string $ip, ?string $channel = null): void
    {
        $result = match ($status) {
            'success' => 'REDEEM_SUCCESS',
            'invalid' => 'REDEEM_INVALID',
            'expired' => 'REDEEM_EXPIRED',
            'used' => 'REDEEM_USED',
            default => 'REDEEM_UNKNOWN',
        };

        $payload = ['status' => $status];
        if ($channel !== null) {
            $payload['channel'] = $channel;
        }

        $this->logger->logAction([
            'actor_id' => $userId ?: null,
            'action' => 'CODE_REDEEM',
            'target_type' => 'cdk_code',
            'target_id' => $code,
            'payload' => $payload,
            'result_code' => $result,
            'ip' => $ip,
        ]);
    }
}

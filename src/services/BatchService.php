<?php

namespace Taisafe\Services;

use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;
use Taisafe\Models\CdkBatch;
use Taisafe\Repositories\DatabaseConnection;
use Taisafe\Services\AuditLogger;

/**
 * Handles batch creation, collision checks, and audit logging (T022, FR-001, FR-006).
 */
class BatchService
{
    private const COLLISION_RETRIES = 3;

    private PDO $pdo;
    private AuditLogger $logger;

    public function __construct(?PDO $pdo = null, ?AuditLogger $logger = null)
    {
        $this->pdo = $pdo ?? DatabaseConnection::get();
        $this->logger = $logger ?? new AuditLogger($this->pdo);
    }

    /**
     * @return array{batch_id:int,created_count:int,expires_at:?string,export_url:string}
     */
    public function createBatch(CdkBatch $batch, int $createdBy): array
    {
        $this->pdo->beginTransaction();
        $now = new DateTimeImmutable('now');
        try {
            $this->assertPatternAvailable($batch->pattern);
            $batchId = $this->insertBatch($batch, $createdBy);
            $createdCount = $this->generateCodes($batchId, $batch, $now);
            $result = [
                'batch_id' => $batchId,
                'created_count' => $createdCount,
                'expires_at' => $batch->expiresAt?->format('Y-m-d H:i:s'),
                'export_url' => sprintf('/api/cdk/batches/%d/export.csv', $batchId),
            ];
            $this->pdo->commit();
            $this->logger->logAction([
                'actor_id' => $createdBy,
                'action' => 'BATCH_CREATE',
                'target_type' => 'cdk_batch',
                'target_id' => (string) $batchId,
                'payload' => ['quantity' => $batch->quantity],
                'result_code' => 'BATCH_CREATED',
            ]);
            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->logger->logAction([
                'actor_id' => $createdBy,
                'action' => 'BATCH_CREATE',
                'target_type' => 'cdk_batch',
                'payload' => ['error' => $e->getMessage()],
                'result_code' => 'BATCH_CREATE_FAILED',
            ]);
            throw $e;
        }
    }

    public function hasExistingBatches(): bool
    {
        $stmt = $this->pdo->query('SELECT 1 FROM cdk_batches LIMIT 1');
        return (bool) $stmt->fetchColumn();
    }

    private function assertPatternAvailable(string $pattern): void
    {
        $stmt = $this->pdo->prepare('SELECT id FROM cdk_batches WHERE pattern = :pattern LIMIT 1');
        $stmt->execute([':pattern' => $pattern]);
        if ($stmt->fetchColumn()) {
            throw new InvalidArgumentException('PATTERN_DUPLICATE');
        }
    }

    private function insertBatch(CdkBatch $batch, int $createdBy): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO cdk_batches (batch_name, pattern, quantity, expires_at, tags, created_by)
             VALUES (:name, :pattern, :quantity, :expires, :tags, :created_by)'
        );
        try {
            $stmt->execute([
                ':name' => $batch->batchName,
                ':pattern' => $batch->pattern,
                ':quantity' => $batch->quantity,
                ':expires' => $batch->expiresAt?->format('Y-m-d H:i:s'),
                ':tags' => json_encode($batch->tags, JSON_UNESCAPED_UNICODE),
                ':created_by' => $createdBy,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new InvalidArgumentException('BATCH_NAME_DUPLICATE', 0, $e);
            }
            throw $e;
        }
        return (int) $this->pdo->lastInsertId();
    }

    private function generateCodes(int $batchId, CdkBatch $batch, DateTimeImmutable $now): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO cdk_codes (batch_id, code, status) VALUES (:batch, :code, :status)'
        );
        $created = 0;
        for ($i = 0; $i < $batch->quantity; $i++) {
            $retries = 0;
            while (true) {
                $code = $this->renderPattern($batch->pattern, $i, $now);
                try {
                    $stmt->execute([
                        ':batch' => $batchId,
                        ':code' => $code,
                        ':status' => 'issued',
                    ]);
                    $created++;
                    break;
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }
                    if ($retries >= self::COLLISION_RETRIES) {
                        throw new RuntimeException('CODE_COLLISION', 0, $e);
                    }
                    $retries++;
                }
            }
        }
        return $created;
    }

    private function renderPattern(string $pattern, int $seq, DateTimeImmutable $now): string
    {
        $code = str_replace('{SEQ}', str_pad((string) ($seq + 1), 4, '0', STR_PAD_LEFT), $pattern);
        $code = preg_replace_callback('/\{RANDOM(\d+)\}/', static function (array $match): string {
            $length = max(4, (int) $match[1]);
            return strtoupper(substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length));
        }, $code);
        return str_replace('{DATE}', $now->format('Ymd'), $code);
    }
}

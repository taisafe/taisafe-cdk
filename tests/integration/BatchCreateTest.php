<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Taisafe\Models\CdkBatch;
use Taisafe\Services\AuditLogger;
use Taisafe\Services\BatchService;

final class BatchCreateTest extends TestCase
{
    private PDO $pdo;
    private BatchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createSchema();
        $this->seedUsers();
        $this->service = new BatchService($this->pdo, new AuditLogger($this->pdo));
    }

    public function testCreateBatchPersistsCodesAndLogs(): void
    {
        $batch = new CdkBatch(
            batchName: 'Campaign A',
            pattern: 'CAMP-{DATE}-{SEQ}',
            quantity: 2,
            expiresAt: new DateTimeImmutable('+1 day'),
            tags: ['launch']
        );

        $result = $this->service->createBatch($batch, 1);

        $this->assertSame(2, (int) $this->pdo->query('SELECT COUNT(*) FROM cdk_codes')->fetchColumn());
        $this->assertSame(2, $result['created_count']);
        $this->assertStringContainsString('/api/cdk/batches/', $result['export_url']);

        $logCode = $this->pdo->query("SELECT result_code FROM audit_log WHERE action = 'BATCH_CREATE'")->fetchColumn();
        $this->assertSame('BATCH_CREATED', $logCode);
    }

    public function testDuplicatePatternThrowsException(): void
    {
        $batch = new CdkBatch('Promo A', 'PROMO-{SEQ}', 1, new DateTimeImmutable('+1 day'));
        $this->service->createBatch($batch, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PATTERN_DUPLICATE');
        $this->service->createBatch(new CdkBatch('Promo B', 'PROMO-{SEQ}', 1, new DateTimeImmutable('+1 day')), 1);
    }

    public function testQuantityOverLimitThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('LIMIT_EXCEEDED');
        new CdkBatch('Too big', 'BIG-{SEQ}', 20000, new DateTimeImmutable('+1 day'));
    }

    public function testZeroStateDetection(): void
    {
        $this->assertFalse($this->service->hasExistingBatches());
        $this->service->createBatch(new CdkBatch('Zero', 'ZERO-{SEQ}', 1, new DateTimeImmutable('+1 day')), 1);
        $this->assertTrue($this->service->hasExistingBatches());
    }

    private function createSchema(): void
    {
        $this->pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "operator"
        )');
        $this->pdo->exec('CREATE TABLE cdk_batches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            batch_name TEXT NOT NULL UNIQUE,
            pattern TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            expires_at TEXT NULL,
            tags TEXT NULL,
            created_by INTEGER NOT NULL
        )');
        $this->pdo->exec('CREATE TABLE cdk_codes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            batch_id INTEGER NOT NULL,
            code TEXT NOT NULL UNIQUE,
            status TEXT NOT NULL,
            redeemed_by INTEGER NULL,
            redeemed_at TEXT NULL
        )');
        $this->pdo->exec('CREATE TABLE audit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            actor_id INTEGER NULL,
            action TEXT NOT NULL,
            target_type TEXT NULL,
            target_id TEXT NULL,
            payload TEXT NULL,
            ip BLOB NULL,
            result_code TEXT NULL,
            created_at TEXT NULL
        )');
    }

    private function seedUsers(): void
    {
        $this->pdo->exec("INSERT INTO users (id, account, role) VALUES (1, 'operator', 'operator')");
    }
}

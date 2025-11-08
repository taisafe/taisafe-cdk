<?php

declare(strict_types=1);

use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Taisafe\Middleware\RoleGuard;
use Taisafe\Services\AuditLogger;
use Taisafe\Services\RedeemService;
use Taisafe\Validators\RedeemValidator;

final class RedeemCodeTest extends TestCase
{
    private PDO $pdo;
    private RedeemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createSchema();
        $this->seedData();
        $this->service = new RedeemService($this->pdo, new RedeemValidator(), new AuditLogger($this->pdo));
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    public function testRedeemSuccessMarksCodeAndLogs(): void
    {
        $result = $this->service->redeem(['code' => 'successcode0000000000000000000000', 'channel' => 'pos'], 1, '127.0.0.1');
        $this->assertSame('success', $result['status']);
        $stmt = $this->pdo->query(\"SELECT status, redeemed_by FROM cdk_codes WHERE code = 'SUCCESSCODE0000000000000000000000'\");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('redeemed', $row['status']);
        $this->assertSame('1', (string) $row['redeemed_by']);

        $logStmt = $this->pdo->query(\"SELECT result_code FROM audit_log WHERE action = 'CODE_REDEEM'\");
        $this->assertSame('REDEEM_SUCCESS', $logStmt->fetchColumn());
    }

    public function testRedeemFailsWhenAlreadyUsed(): void
    {
        $result = $this->service->redeem(['code' => 'usedcode000000000000000000000000', 'channel' => 'pos'], 1);
        $this->assertSame('used', $result['status']);

        $logStmt = $this->pdo->query(\"SELECT result_code FROM audit_log WHERE action = 'CODE_REDEEM'\");
        $this->assertSame('REDEEM_USED', $logStmt->fetchColumn());
    }

    public function testRedeemFailsWhenBatchExpired(): void
    {
        $result = $this->service->redeem(['code' => 'expiredcode000000000000000000000', 'channel' => 'pos'], 1);
        $this->assertSame('expired', $result['status']);

        $logStmt = $this->pdo->query(\"SELECT result_code FROM audit_log WHERE action = 'CODE_REDEEM'\");
        $this->assertSame('REDEEM_EXPIRED', $logStmt->fetchColumn());
    }

    public function testRedeemRequiresOperatorRole(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_id('redeem-role-' . uniqid());
        session_start();
        $_SESSION['role'] = 'auditor';
        $_SESSION['user_id'] = 42;

        $guard = new RoleGuard();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SEC_DENY_ROLE');
        $guard->enforce(['admin', 'operator']);
    }

    private function createSchema(): void
    {
        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, account TEXT, password_hash TEXT, role TEXT, status TEXT)');
        $this->pdo->exec('CREATE TABLE cdk_batches (id INTEGER PRIMARY KEY AUTOINCREMENT, batch_name TEXT, pattern TEXT, quantity INTEGER, expires_at TEXT, created_by INTEGER)');
        $this->pdo->exec('CREATE TABLE cdk_codes (id INTEGER PRIMARY KEY AUTOINCREMENT, batch_id INTEGER, code TEXT UNIQUE, status TEXT, redeemed_by INTEGER NULL, redeemed_at TEXT NULL)');
        $this->pdo->exec('CREATE TABLE audit_log (id INTEGER PRIMARY KEY AUTOINCREMENT, actor_id INTEGER NULL, action TEXT, target_type TEXT, target_id TEXT, payload TEXT, ip BLOB, result_code TEXT)');
    }

    private function seedData(): void
    {
        $this->pdo->exec(\"INSERT INTO users (id, account, password_hash, role, status) VALUES (1, 'operator', 'hash', 'operator', 'active')\");
        $activeExpires = (new DateTimeImmutable('+1 day'))->format('Y-m-d H:i:s');
        $expiredDate = (new DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s');

        $this->pdo->exec(sprintf(
            \"INSERT INTO cdk_batches (id, batch_name, pattern, quantity, expires_at, created_by) VALUES (1, 'ACTIVE', 'PATTERN', 1, '%s', 1)\",
            $activeExpires
        ));
        $this->pdo->exec(sprintf(
            \"INSERT INTO cdk_batches (id, batch_name, pattern, quantity, expires_at, created_by) VALUES (2, 'EXPIRED', 'PATTERN', 1, '%s', 1)\",
            $expiredDate
        ));

        $this->pdo->exec(\"INSERT INTO cdk_codes (batch_id, code, status) VALUES (1, 'SUCCESSCODE0000000000000000000000', 'issued')\");
        $this->pdo->exec(\"INSERT INTO cdk_codes (batch_id, code, status, redeemed_by, redeemed_at) VALUES (1, 'USEDCODE000000000000000000000000', 'redeemed', 1, '2024-01-01 00:00:00')\");
        $this->pdo->exec(\"INSERT INTO cdk_codes (batch_id, code, status) VALUES (2, 'EXPIREDCODE000000000000000000000', 'issued')\");
    }
}

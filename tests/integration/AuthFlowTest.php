<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Taisafe\Repositories\UserRepository;
use Taisafe\Services\AuthService;
use Taisafe\Middleware\SessionMiddleware;

final class AuthFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('INVALID_CREDENTIALS');
        $service = new AuthService(new UserRepository());
        $service->login('nonexistent', 'wrong');
    }

    public function testSessionTimeoutTriggersException(): void
    {
        $middleware = new SessionMiddleware();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SESSION_EXPIRED');
        $_SESSION['last_activity'] = time() - 4000;
        $middleware->start();
    }
}

<?php

namespace Taisafe\Repositories;

use PDO;
use RuntimeException;

class UserRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DatabaseConnection::get();
    }

    public function findByAccount(string $account): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE account = :account LIMIT 1');
        $stmt->execute([':account' => $account]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $userId]);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (account, password_hash, role, status) VALUES (:account, :password_hash, :role, :status)');
        if (! $stmt->execute([
            ':account' => $data['account'],
            ':password_hash' => $data['password_hash'],
            ':role' => $data['role'],
            ':status' => $data['status'] ?? 'active',
        ])) {
            throw new RuntimeException('Unable to create user');
        }
        return (int) $this->pdo->lastInsertId();
    }
}

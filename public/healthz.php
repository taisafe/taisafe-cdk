<?php

use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

// Health endpoint (T014).
header('Content-Type: application/json');
$status = ['status' => 'ok'];
try {
    require_once __DIR__ . '/../src/repositories/DatabaseConnection.php';
    $pdo = Taisafe\Repositories\DatabaseConnection::get();
    $pdo->query('SELECT 1');
    $status['db'] = 'reachable';
} catch (Throwable $e) {
    http_response_code(500);
    $status['status'] = 'error';
    $status['db'] = $e->getMessage();
}
$status['timestamp'] = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
$status['request_id'] = bin2hex(random_bytes(6)); // for log correlation

echo json_encode($status);

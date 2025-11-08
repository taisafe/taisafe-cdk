<?php

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use Taisafe\Middleware\RoleGuard;
use Taisafe\Services\RedeemService;

require_once __DIR__ . '/../../../tests/bootstrap.php';

header('Content-Type: application/json');

try {
    $guard = new RoleGuard();
    $guard->enforce(['admin', 'operator']);

    $service = new RedeemService();
    $input = resolveInput();
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $result = $service->redeem($input, $userId, $ip);
    echo json_encode($result);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'code' => $e->getMessage()]);
} catch (RuntimeException $e) {
    $status = $e->getMessage() === 'SEC_DENY_ROLE' ? 403 : 400;
    http_response_code($status);
    echo json_encode(['status' => 'error', 'code' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'code' => 'SERVER_ERROR']);
}

/**
 * @return array<string,mixed>
 */
function resolveInput(): array
{
    if (! empty($_POST)) {
        return $_POST;
    }
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw ?? '', true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return [];
}

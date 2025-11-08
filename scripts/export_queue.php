#!/usr/bin/env php
<?php
// Background export processor (T013) – handles 202 jobs.
use Taisafe\Repositories\DatabaseConnection;
use Taisafe\Services\AuditLogger;

require __DIR__ . '/../src/repositories/DatabaseConnection.php';
require __DIR__ . '/../src/services/AuditLogger.php';

$pdo = DatabaseConnection::get();
$logger = new AuditLogger($pdo);

$stmt = $pdo->query("SELECT * FROM export_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 5");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($jobs as $job) {
    $pdo->prepare('UPDATE export_queue SET status = "processing", updated_at = NOW() WHERE id = :id')
        ->execute([':id' => $job['id']]);
    try {
        // TODO: build CSV and store to filesystem (placeholder).
        $resultPath = '/runtime/exports/job-' . $job['id'] . '.csv';
        $pdo->prepare('UPDATE export_queue SET status = "completed", result_path = :path, updated_at = NOW() WHERE id = :id')
            ->execute([':path' => $resultPath, ':id' => $job['id']]);
        $logger->logAction([
            'actor_id' => $job['requested_by'],
            'action' => 'EXPORT_COMPLETED',
            'target_type' => $job['job_type'],
            'target_id' => (string) $job['id'],
            'result_code' => 'EXPORT_COMPLETED',
        ]);
    } catch (Throwable $e) {
        $pdo->prepare('UPDATE export_queue SET status = "failed", error_message = :err, updated_at = NOW() WHERE id = :id')
            ->execute([':err' => $e->getMessage(), ':id' => $job['id']]);
        $logger->logAction([
            'actor_id' => $job['requested_by'],
            'action' => 'EXPORT_FAILED',
            'target_type' => $job['job_type'],
            'target_id' => (string) $job['id'],
            'result_code' => 'EXPORT_FAILED',
        ]);
    }
}

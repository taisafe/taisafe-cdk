<?php

namespace Taisafe\Controllers;

use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;
use Taisafe\Middleware\RoleGuard;
use Taisafe\Models\CdkBatch;
use Taisafe\Services\BatchService;

class BatchController
{
    public function __construct(private BatchService $service = new BatchService(), private RoleGuard $guard = new RoleGuard())
    {
    }

    /**
     * @return array{batch_id:int,created_count:int,expires_at:?string,export_url:string}
     */
    public function create(array $input, int $userId): array
    {
        $this->guard->enforce(['admin', 'operator']);
        $batch = new CdkBatch(
            batchName: $this->requireString($input, 'batch_name', 'BATCH_NAME_REQUIRED'),
            pattern: $this->requireString($input, 'pattern', 'PATTERN_REQUIRED'),
            quantity: (int) ($input['quantity'] ?? 0),
            expiresAt: $this->parseDate($input['expires_at'] ?? null),
            tags: $this->parseTags($input['tags'] ?? [])
        );
        return $this->service->createBatch($batch, $userId);
    }

    private function requireString(array $input, string $key, string $errorCode): string
    {
        $value = trim((string) ($input[$key] ?? ''));
        if ($value === '') {
            throw new InvalidArgumentException($errorCode);
        }
        return $value;
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            throw new InvalidArgumentException('EXPIRES_AT_INVALID');
        }
    }

    /**
     * @return string[]
     */
    private function parseTags(mixed $value): array
    {
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }
        if (! is_array($value)) {
            return [];
        }
        $tags = [];
        foreach ($value as $tag) {
            if (! is_string($tag)) {
                continue;
            }
            $trimmed = trim($tag);
            if ($trimmed !== '') {
                $tags[] = $trimmed;
            }
        }
        return $tags;
    }
}

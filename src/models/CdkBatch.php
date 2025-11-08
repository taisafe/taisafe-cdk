<?php

namespace Taisafe\Models;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Value object encapsulating batch validation rules (T021, FR-001, EC-001, EC-002).
 */
class CdkBatch
{
    private const MIN_NAME_LENGTH = 3;
    private const MAX_NAME_LENGTH = 64;
    private const MAX_PATTERN_LENGTH = 128;
    private const MAX_TAGS = 5;
    private const MAX_TAG_LENGTH = 32;

    public function __construct(
        public string $batchName,
        public string $pattern,
        public int $quantity,
        public ?DateTimeInterface $expiresAt = null,
        public array $tags = []
    ) {
        $this->batchName = $this->sanitizeName($batchName);
        $this->pattern = $this->sanitizePattern($pattern);
        $this->quantity = $this->guardQuantity($quantity);
        $this->expiresAt = $this->guardExpiration($expiresAt);
        $this->tags = $this->sanitizeTags($tags);
    }

    private function sanitizeName(string $name): string
    {
        $trimmed = trim($name);
        $length = strlen($trimmed);
        if ($length < self::MIN_NAME_LENGTH || $length > self::MAX_NAME_LENGTH) {
            throw new InvalidArgumentException('BATCH_NAME_INVALID');
        }
        return $trimmed;
    }

    private function sanitizePattern(string $pattern): string
    {
        $normalized = strtoupper(trim($pattern));
        $length = strlen($normalized);
        if ($normalized === '' || $length > self::MAX_PATTERN_LENGTH) {
            throw new InvalidArgumentException('PATTERN_INVALID');
        }
        if (! preg_match('/^[A-Z0-9\\-{}]+$/', $normalized)) {
            throw new InvalidArgumentException('PATTERN_INVALID');
        }
        $this->assertPlaceholders($normalized);
        return $normalized;
    }

    private function assertPlaceholders(string $pattern): void
    {
        if (! preg_match('/\\{(DATE|SEQ|RANDOM\\d+)\\}/', $pattern)) {
            throw new InvalidArgumentException('PATTERN_PLACEHOLDER_REQUIRED');
        }
        $hasUniqueToken = str_contains($pattern, '{SEQ}') || preg_match('/\\{RANDOM\\d+\\}/', $pattern) === 1;
        if (! $hasUniqueToken) {
            throw new InvalidArgumentException('PATTERN_INVALID');
        }
        if (preg_match_all('/\\{RANDOM(\\d+)\\}/', $pattern, $matches)) {
            foreach ($matches[1] as $length) {
                $len = (int) $length;
                if ($len < 4 || $len > 32) {
                    throw new InvalidArgumentException('PATTERN_INVALID');
                }
            }
        }
    }

    private function guardQuantity(int $quantity): int
    {
        if ($quantity < 1 || $quantity > 10000) {
            throw new InvalidArgumentException('LIMIT_EXCEEDED');
        }
        return $quantity;
    }

    private function guardExpiration(?DateTimeInterface $expiresAt): ?DateTimeImmutable
    {
        if ($expiresAt === null) {
            return null;
        }
        $expiry = DateTimeImmutable::createFromInterface($expiresAt);
        if ($expiry < new DateTimeImmutable('now')) {
            throw new InvalidArgumentException('EXPIRES_AT_PAST');
        }
        return $expiry;
    }

    private function sanitizeTags(array $tags): array
    {
        $clean = [];
        foreach ($tags as $tag) {
            if (! is_string($tag)) {
                continue;
            }
            $value = trim($tag);
            if ($value === '') {
                continue;
            }
            $clean[] = substr($value, 0, self::MAX_TAG_LENGTH);
            if (count($clean) >= self::MAX_TAGS) {
                break;
            }
        }
        return array_values(array_unique($clean));
    }
}

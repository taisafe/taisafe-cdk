<?php

namespace Taisafe\Validators;

use DateTimeImmutable;
use InvalidArgumentException;

class RedeemValidator
{
    /**
     * Normalize and validate redeem request payload.
     *
     * @param array{code?:string,channel?:string} $input
     * @return array{code:string,channel:string}
     */
    public function validateInput(array $input): array
    {
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        if ($code === '' || ! preg_match('/^[A-Z0-9-]{8,32}$/', $code)) {
            throw new InvalidArgumentException('CODE_INVALID_FORMAT');
        }
        $channel = trim((string) ($input['channel'] ?? ''));
        if ($channel === '') {
            throw new InvalidArgumentException('CHANNEL_REQUIRED');
        }

        return ['code' => $code, 'channel' => $channel];
    }

    /**
     * Determine redemption state based on record and expiration info.
     *
     * @param array{id?:int,status?:string,batch_expires_at?:string|null}|null $record
     */
    public function determineState(?array $record, DateTimeImmutable $now): string
    {
        if ($record === null) {
            return 'invalid';
        }

        $status = $record['status'] ?? 'issued';
        if ($status === 'expired') {
            return 'expired';
        }
        if (in_array($status, ['redeemed', 'revoked', 'audited'], true)) {
            return 'used';
        }

        $expiresAt = $record['batch_expires_at'] ?? null;
        if ($expiresAt !== null) {
            $expiry = new DateTimeImmutable($expiresAt);
            if ($expiry < $now) {
                return 'expired';
            }
        }

        return 'ready';
    }
}

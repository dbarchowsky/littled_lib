<?php
declare(strict_types=1);
namespace Littled\Utility;

final class RateLimiter
{
    /**
     * Token bucket rate limit.
     *
     * @param string $key        Unique key like "contact-form:<ip>"
     * @param int    $capacity   Max burst size (tokens)
     * @param float  $refillRate Tokens per second (e.g., 5/min = 5/60)
     * @param int    $ttlSeconds Storage TTL for idle buckets
     *
     * @return array{allowed: bool, retry_after: int, remaining: int}
     */
    public static function tokenBucketApcu(
        string $key,
        int $capacity = 3,
        float $refillRate = 6.0 / 60.0,
        int $ttlSeconds = 3600
    ): array {
        if (!function_exists('apcu_fetch') || !filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOL)) {
            // Fail-safe policy choice:
            // - allow if limiter unavailable (avoid blocking legit traffic)
            // - OR deny (safer against abuse). Usually allow + log.
            return ['allowed' => true, 'retry_after' => 0, 'remaining' => $capacity];
        }

        $now = microtime(true);
        $apcuKey = 'rl:' . hash('sha256', $key);

        $state = apcu_fetch($apcuKey, $ok);
        if (!$ok || !is_array($state)) {
            $state = [
                'tokens' => (float) $capacity,
                'ts'     => $now,
            ];
        }

        $tokens = (float)($state['tokens'] ?? $capacity);
        $ts     = (float)($state['ts'] ?? $now);

        // Refill
        $elapsed = max(0.0, $now - $ts);
        $tokens = min((float)$capacity, $tokens + ($elapsed * $refillRate));

        if ($tokens >= 1.0) {
            $tokens -= 1.0;

            apcu_store($apcuKey, ['tokens' => $tokens, 'ts' => $now], $ttlSeconds);

            return [
                'allowed'     => true,
                'retry_after' => 0,
                'remaining'   => (int)floor($tokens),
            ];
        }

        // Not allowed; compute retry-after until the next token
        $secondsUntilNext = (int)ceil((1.0 - $tokens) / max($refillRate, 0.000001));
        apcu_store($apcuKey, ['tokens' => $tokens, 'ts' => $now], $ttlSeconds);

        return [
            'allowed'     => false,
            'retry_after' => max(1, $secondsUntilNext),
            'remaining'   => 0,
        ];
    }
}

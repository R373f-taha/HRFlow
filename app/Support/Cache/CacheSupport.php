<?php

namespace App\Support\Cache;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class CacheSupport
{
    /**
     * Retrieve an item from the cache or execute the given Closure with an atomic lock
     * to protect against Cache Stampede (Thundering Herd Problem).
     *
     * @template T
     * @param string $key
     * @param int $ttlInSeconds
     * @param Closure(): T $callback
     * @param int $lockWaitSeconds
     * @return T
     */
    public static function remember(string $key, int $ttlInSeconds, Closure $callback, int $lockWaitSeconds = 5)
    {
        // Step 1: Fast path - Attempt to retrieve data directly from cache.
        $cachedData = Cache::get($key);
        if ($cachedData !== null) {
            return $cachedData;
        }

        // Step 2: Cache Miss - Acquire an atomic lock to prevent concurrent database queries.
        $lockKey = "lock:{$key}";
        $lock = Cache::lock($lockKey, $lockWaitSeconds);
try {
        return Cache::lock($lockKey, $lockWaitSeconds)->block($lockWaitSeconds, function () use ($key, $ttlInSeconds, $callback) {
            $cachedData = Cache::get($key);
            if ($cachedData !== null) {
                return $cachedData;
            }

            $freshData = $callback();

            if ($freshData !== null) {
                Cache::put($key, $freshData, $ttlInSeconds);
            }

            return $freshData;
        });
    } catch (LockTimeoutException $e) {
        // Fallback: If lock acquisition times out, fetch fresh data directly
        return $callback();
    }
    }

    /**
     * Remove a single key from cache.
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Remove multiple keys from cache.
     *
     * @param array<int, string> $keys
     */
    public static function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}

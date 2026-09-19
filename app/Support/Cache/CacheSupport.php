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
        // 1. Fast path - Attempt to retrieve data directly from cache.
        $cachedData = Cache::get($key);
        if ($cachedData !== null) {
            return $cachedData;
        }

        // 2. Cache Miss - Acquire atomic lock cleanly
        $lockKey = "lock:{$key}";

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

            return Cache::get($key) ?? $callback();
        }
    }

    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    public static function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}

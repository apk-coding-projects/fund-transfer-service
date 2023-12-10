<?php

declare(strict_types=1);

namespace src\Common\Helpers;

use src\Common\Exceptions\LockAlreadyAcquiredException;

class CacheLockHelper
{
    /**
     * @param  int  $ttl  seconds for lock to be acquired
     * @throws LockAlreadyAcquiredException
     */
    public static function acquire(string $key, int $ttl): bool
    {
        $lock = RedisCacheHelper::get($key);
        if ($lock) {
            throw new LockAlreadyAcquiredException("Lock with key '$key' is already acquired by another process");
        }

        RedisCacheHelper::set($key, true, $ttl);

        return true;
    }

    public static function release(string $key): void
    {
        RedisCacheHelper::delete($key);
    }
}

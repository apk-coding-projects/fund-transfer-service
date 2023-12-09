<?php

declare(strict_types=1);

namespace src\Common\Helpers;

use Illuminate\Support\Facades\Redis;

class RedisCacheHelper
{
    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $ttl  - durations in seconds till cache expires
     * @return void
     */
    public static function set(string $key, mixed $value, int $ttl = 60): void
    {
        Redis::setex($key, $ttl, $value);
    }

    public static function get(string $key): mixed
    {
        return Redis::get($key);
    }

    public static function delete(string $key): void
    {
        Redis::del($key);
    }
}

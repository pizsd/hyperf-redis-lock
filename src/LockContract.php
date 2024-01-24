<?php

declare(strict_types=1);

namespace Pizsd\HyperfRedisLock;

interface LockContract
{
    /**
     * Attempt to acquire the lock.
     *
     * @return mixed
     */
    public function get(string $key, int $ttl, ?\Closure $callback = null);

    /**
     * Attempt to acquire the lock for the given number of seconds.
     * @param $ttl millisecond
     * @return mixed
     */
    public function block(string $key, int $ttl, ?\Closure $callback = null);

    /**
     * Release the lock.
     *
     * @return mixed
     */
    public function release(string $key, string $owner);

    /**
     * Releases this lock in disregard of ownership.
     *
     * @return mixed
     */
    public function forceRelease(string $key);
}

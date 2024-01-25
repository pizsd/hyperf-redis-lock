<?php

declare(strict_types=1);

namespace Pizsd\HyperfRedisLock;

use Hyperf\Utils\InteractsWithTime;

abstract class Lock implements LockContract
{
    use InteractsWithTime;

    /**
     * Attempt to acquire the lock.
     *
     * @param int $ttl millisecond
     */
    abstract public function acquire(string $key, int $ttl): string|false;

    /**
     * Release the lock.
     *
     * @return mixed
     */
    abstract public function release(string $key, string $owner): bool;

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    public function get(string $key, int $ttl, $callback = null, $finally = null): bool
    {
        $owner = $this->acquire($key, $ttl);
        if ($owner && is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release($key, $owner);
            }
        }
        if (!$owner && is_callable($finally)) {
            return $finally();
        }

        return (bool)$owner;
    }

    /**
     * Blocking lock.
     *
     * @param int $ttl millisecond
     *
     * @throws LockTimeoutException
     */
    public function block(string $key, int $ttl, ?\Closure $callback = null): mixed
    {
        $starting = $this->currentTime();
        $owner = $this->acquire($key, $ttl);
        while (!$owner) {
            usleep(250 * 1000);
            if ($this->currentTime() - ($ttl / 1000) >= $starting) {
                throw new LockTimeoutException();
            }
        }

        if (is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release($key, $owner);
            }
        }

        return true;
    }

    /**
     * Returns the owner value written into the driver for this lock.
     */
    abstract protected function getCurrentOwner(string $key): string|false;
}

<?php

declare (strict_types=1);

namespace Pizsd\HyperfRedisLock;

use Hyperf\Utils\Str;
use Hyperf\Utils\InteractsWithTime;

abstract class Lock implements LockContract
{
    use InteractsWithTime;

    /**
     * The scope identifier of this lock
     * 
     * @var string
     */
    protected ?string $owner;

    public function __construct($owner = null)
    {
        if (is_null($owner)) {
            $owner = Str::random();
        }
        $this->owner = $owner;
    }

    /**
     * Attempt to acquire the lock
     *
     * @param string $key
     * @param int $ttl millisecond
     * @return bool
     */
    abstract public function acquire(string $key, int $ttl): bool;

    /**
     * Release the lock
     *
     * @param string $key
     * @return mixed
     * @author: FatTiger{佛祖保佑代码永无BUG}
     */
    abstract public function release(string $key);

    /**
     * Returns the owner value written into the driver for this lock
     *
     * @param string $key
     * @return string
     */
    abstract protected function getCurrentOwner(string $key): string;

    /**
     * Attempt to acquire the lock
     *
     * @param string $key
     * @param int $ttl
     * @param $callback
     * @param $finally
     * @return bool
     */
    public function get(string $key, int $ttl, $callback = null, $finally = null)
    {
        $result = $this->acquire($key, $ttl);
        if ($result && is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release($key);
            }
        }
        if (!$result && is_callable($finally)) {
            return $finally();
        }

        return $result;
    }

    /**
     * Blocking lock
     * 
     * @param string $key
     * @param int $ttl millisecond
     * @param \Closure|null $callback
     * @return mixed
     * @throws LockTimeoutException
     */
    public function block(string $key, int $ttl, ?\Closure $callback = null): mixed
    {
        $starting = $this->currentTime();
        while (!$this->acquire($key, $ttl)) {
            usleep(250 * 1000);
            if ($this->currentTime() - ($ttl/1000) >= $starting) {
                throw new LockTimeoutException();
            }
        }

        if (is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release($key);
            }
        }

        return true;
    }

    /**
     * Returns the current owner of the lock.
     *
     * @return string
     */
    public function owner(): string
    {
        return $this->owner;
    }

    /**
     * Determines whether this lock is allowed to release the lock in the driver.
     * @param string $key
     * @return bool
     */
    protected function isOwnedByCurrentProcess(string $key): bool
    {
        return $this->getCurrentOwner($key) === $this->owner;
    }
}

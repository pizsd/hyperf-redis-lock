<?php

declare(strict_types=1);

namespace Pizsd\HyperfRedisLock;

use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;

class RedisLock extends Lock
{
    #[Inject]
    protected Redis $redis;

    /**
     * @param int $ttl millisecond
     *
     * @throws \RedisException
     */
    public function acquire(string $key, int $ttl): string|false
    {
        $owner = uniqid(gethostname(), true);
        if ($this->redis->set($key, $owner, ['NX', 'PX' => $ttl])) {
            return $owner;
        }

        return false;
    }

    /**
     * @throws \RedisException
     */
    public function release(string $key, string $owner): bool
    {
        if ($value === $owner) {
            $res = $this->redis->eval(LockScripts::releaseLock(), ['name' => $key, 'owner' => $owner], 1);

            return 1 == $res;
        }

        // The lock expires or is not the lock holder
        return false;
    }

    /**
     * @throws \RedisException
     */
    public function forceRelease(string $key): bool
    {
        $r = $this->redis->del($key);

        return 1 == $r;
    }

    /**
     * @throws \RedisException
     */
    protected function getCurrentOwner(string $key): string|false
    {
        return $this->redis->get($key);
    }
}

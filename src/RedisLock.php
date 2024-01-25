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
        // The lock has expired when it is released.
        if (!$owner) {
            return true;
        }
        if ($this->getCurrentOwner($key) === $owner) {
            $res = $this->redis->eval(LockScripts::releaseLock(), ['name' => $key, 'owner' => $owner], 1);

            return 1 == $res;
        }

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

<?php

declare (strict_types=1);

namespace Pizsd\HyperfRedisLock;

use Hyperf\Redis\Redis;

class RedisLock extends Lock
{

    #[Inject]
    protected Redis $redis;

    public function __construct($owner = null)
    {
        parent::__construct($owner);
    }

    /**
     * @param string $key
     * @param int $ttl millisecond
     * @return bool
     * @throws \RedisException
     */
    public function acquire(string $key, int $ttl): bool
    {
        return $this->redis->set($key, $this->owner, ['NX', 'PX' => $ttl]);
    }

    /**
     * @param string $key
     * @return bool
     * @throws \RedisException
     */
    public function release(string $key): bool
    {
        if ($this->isOwnedByCurrentProcess($key)) {
            $res = $this->redis->eval(LockScripts::releaseLock(), ['name' => $key, 'owner' => $this->owner], 1);
            return $res == 1;
        }
        return false;
    }

    /**
     * @param string $key
     * @return string
     * @throws \RedisException
     */
    protected function getCurrentOwner(string $key): string
    {
        return $this->redis->get($key);
    }

    /**
     * @param string $key
     * @return bool
     * @throws \RedisException
     */
    public function forceRelease(string $key): bool
    {
        $r = $this->redis->del($key);
        return $r == 1;
    }
}

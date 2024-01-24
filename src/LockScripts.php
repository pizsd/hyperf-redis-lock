<?php

declare (strict_types = 1);

namespace Pizsd\HyperfRedisLock;

class LockScripts
{
    /**
     * @return string
     */
    public static function releaseLock(): string
    {
        return <<<'LUA'
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
LUA;
    }
}
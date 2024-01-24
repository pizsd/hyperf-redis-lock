<?php

declare(strict_types=1);

namespace Pizsd\HyperfRedisLock;

class LockTimeoutException extends \Exception
{
    public function __construct(int $code = 429, string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

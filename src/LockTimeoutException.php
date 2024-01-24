<?php

declare (strict_types = 1);

namespace Pizsd\HyperfRedisLock;

use Throwable;

class LockTimeoutException extends \Exception
{
    function __construct(int $code = 429, string $message = "", ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
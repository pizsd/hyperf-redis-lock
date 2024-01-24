<?php

declare(strict_types=1);

namespace Pizsd\HyperfRedisLock;

use Carbon\Carbon;

trait InteractsWithTime
{
    /**
     * Get the number of seconds Until the given Datetime.
     *
     * @return int|mixed
     */
    protected function secondsUtil($delay): mixed
    {
        $delay = $this->getDateTimeAfterInterval($delay);

        return $delay instanceof \DateTimeInterface
            ? max(0, $delay->getTimestamp() - $this->currentTime())
            : (int) ($delay);
    }

    /**
     * @param \DateInterval|\DateTimeInterface|int $delay
     */
    protected function availableAt($delay = 0): int
    {
        $delay = $this->getDateTimeAfterInterval($delay);

        return $delay instanceof \DateTimeInterface
            ? $delay->getTimestamp()
            : Carbon::now()->addRealSeconds($delay)->getTimestamp();
    }

    /**
     * Get DateTime Instance from an interval.
     */
    protected function getDateTimeAfterInterval($delay): Carbon
    {
        if ($delay instanceof \DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get current Time.
     */
    protected function currentTime(): int
    {
        return Carbon::now()->getTimestamp();
    }
}

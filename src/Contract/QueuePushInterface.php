<?php

namespace Mizmoz\Queue\Contract;

interface QueuePushInterface
{
    /**
     * Push a message on to the queue
     *
     * @param PayloadInterface $job
     * @param int $delay Delay in seconds before the job should be executed
     * @return bool
     */
    public function push(PayloadInterface $job, int $delay = 0): bool;
}
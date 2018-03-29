<?php

namespace Mizmoz\Queue\Contract;

interface ListenerInterface
{
    /**
     * Listen for work on the provided queue
     *
     * @param string $queue
     * @param int $maxAttempts
     * @param int $maxMemory
     * @param int $waitInterval
     * @param int $maxJobs
     * @return void
     */
    public function listen(
        string $queue = 'default',
        int $maxAttempts = QueueProcessInterface::DEFAULT_MAX_ATTEMPTS,
        int $maxMemory = QueueProcessInterface::DEFAULT_MAX_MEMORY,
        int $waitInterval = QueueProcessInterface::DEFAULT_WAIT_INTERVAL,
        int $maxJobs = 0
    );
}
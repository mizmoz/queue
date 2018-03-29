<?php

namespace Mizmoz\Queue\Contract;

interface QueueProcessInterface
{
    /**
     * Maximum attempts at a job
     */
    const DEFAULT_MAX_ATTEMPTS = 1;

    /**
     * Amount of memory to use before restarting the process.
     */
    const DEFAULT_MAX_MEMORY = 96;

    /**
     * Number of seconds to wait between checking the queue for new jobs
     */
    const DEFAULT_WAIT_INTERVAL = 5;

    /**
     * Process the queue
     *
     * @param int $maxAttempts
     * @param int $maxMemory Max memory in Mb to use before quitting
     * @param int $maxJobs Max jobs to process
     * @param int $waitInterval Interval between polling after the job queue is cleared
     */
    public function process(
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        int $maxMemory = self::DEFAULT_MAX_MEMORY,
        int $waitInterval = self::DEFAULT_WAIT_INTERVAL,
        int $maxJobs = 0
    );

    /**
     * Process a single item in the queue using pop();
     *
     * @param int $maxAttempts
     */
    public function processOne(int $maxAttempts = 1);
}
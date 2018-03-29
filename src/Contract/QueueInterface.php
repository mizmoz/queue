<?php

namespace Mizmoz\Queue\Contract;

interface QueueInterface
{
    /**
     * Mark the job as complete
     *
     * @param JobInterface $job
     * @return bool
     */
    public function complete(JobInterface $job): bool;

    /**
     * Mark the job as failed
     *
     * @param JobInterface $job
     * @return bool
     */
    public function fail(JobInterface $job): bool;

    /**
     * Push a message on to the queue
     *
     * @param JobInterface $job
     * @param int $delay Delay in seconds before the job should be executed
     * @return bool
     */
    public function push(JobInterface $job, int $delay = 0): bool;

    /**
     * Remove the next message from the queue and return it
     *
     * @return JobInterface
     */
    public function pop(): JobInterface;

    /**
     * Watch the queue for messages by polling pop(). This should continually return jobs until the queue
     * is empty and then starting polling every $waitInterval ms.
     *
     * @param int $waitInterval Interval in seconds.
     * @return JobInterface
     */
    public function watch(int $waitInterval = 5): JobInterface;

    /**
     * Put the job back on to the queue.
     *
     * If this is due to a failed attempt at processing make sure the $attempts number is incremented on
     * the $job before releasing.
     *
     * @param JobInterface $job
     * @return bool
     */
    public function release(JobInterface $job): bool;

    /**
     * Return the number of items left to process in the queue
     *
     * @return int
     */
    public function count(): int;

    /**
     * Delete the queue
     *
     * @return bool
     */
    public function delete(): bool;
}
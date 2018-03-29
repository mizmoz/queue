<?php

namespace Mizmoz\Queue;

use Exception;
use Mizmoz\Container\ManageContainerTrait;
use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Contract\QueueProcessInterface;
use Mizmoz\Queue\Exception\JobException;
use Mizmoz\Queue\Exception\JobMaxAttemptsReachedException;
use Mizmoz\Queue\Exception\QueueIsEmptyException;

class Processor
{
    use ManageContainerTrait;

    const STATUS_STOPPED = 1;
    const STATUS_MAX_JOBS_PROCESSED = 2;
    const STATUS_MAX_MEMORY_REACHED = 3;
    const STATUS_NOTHING_TO_PROCESS = 10;

    const STOPPED_STATUSES = [
        self::STATUS_STOPPED,
        self::STATUS_MAX_JOBS_PROCESSED,
        self::STATUS_MAX_MEMORY_REACHED,
    ];

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var int
     */
    private $maxAttempts;

    /**
     * @var int
     */
    private $maxMemory;

    /**
     * @var int
     */
    private $maxJobs;

    /**
     * @var int
     */
    private $waitInterval;

    /**
     * @var bool
     */
    private $running = true;

    /**
     * @var int
     */
    private $processed = 0;

    /**
     * @var int
     */
    private $failed = 0;

    /**
     * @var int
     */
    private $completed = 0;

    /**
     * Processor constructor.
     * @param QueueInterface $queue
     * @param int $maxAttempts
     * @param int $maxMemory
     * @param int $waitInterval
     * @param int $maxJobs
     */
    public function __construct(
        QueueInterface $queue,
        int $maxAttempts = QueueProcessInterface::DEFAULT_MAX_ATTEMPTS,
        int $maxMemory = QueueProcessInterface::DEFAULT_MAX_MEMORY,
        int $waitInterval = QueueProcessInterface::DEFAULT_WAIT_INTERVAL,
        int $maxJobs = 0
    ) {
        $this->queue = $queue;
        $this->maxAttempts = $maxAttempts;
        $this->maxMemory = $maxMemory * 1024 * 1024;
        $this->waitInterval = $waitInterval;
        $this->maxJobs = $maxJobs;

        // register the signal handlers so we can gracefully stop the process
        $this->registerSigHandlers();
    }

    /**
     * Register the signal handlers
     */
    private function registerSigHandlers()
    {
        declare(ticks = 1);

        pcntl_signal(SIGINT, function () {
            $this->running = false;
        });

        pcntl_signal(SIGTERM, function () {
            $this->running = false;
        });
    }

    /**
     * Watch the queue
     *
     * @param bool $justOne
     * @return JobInterface|int
     */
    private function next(bool $justOne = false)
    {
        if (! $this->running) {
            return self::STATUS_STOPPED;
        }

        if ($this->maxJobs && $this->processed >= $this->maxJobs) {
            return self::STATUS_MAX_JOBS_PROCESSED;
        }

        if ($justOne && $this->processed) {
            return self::STATUS_MAX_JOBS_PROCESSED;
        }

        if (memory_get_usage() >= $this->maxMemory) {
            return self::STATUS_MAX_MEMORY_REACHED;
        }

        try {
            // get the next job
            $job = ($justOne ? $this->queue->pop() : $this->queue->watch($this->waitInterval));
        } catch (QueueIsEmptyException $e) {
            return self::STATUS_NOTHING_TO_PROCESS;
        }

        $this->processed++;

        return $job;
    }

    /**
     * Process the queue
     *
     * @param bool $justOne Process just 1 job using pop. This will return immediately if there are no jobs to process
     * @return int
     */
    public function process(bool $justOne = false): int
    {
        // watch the queue for any jobs to process
        while (true) {
            $job = $this->next($justOne);

            if ($job === self::STATUS_STOPPED
                || $job === self::STATUS_MAX_JOBS_PROCESSED
                || $job === self::STATUS_MAX_MEMORY_REACHED) {
                // we've stopped for some reason
                break;
            }

            if ($job === self::STATUS_NOTHING_TO_PROCESS) {
                echo number_format(memory_get_usage()) . PHP_EOL;
                continue;
            }

            try {
                if ((php_sapi_name() === 'cli')) {
                    echo 'Processing job: ' . get_class($job->getPayload()) . PHP_EOL;
                }

                // attempt to run the job
                $job->attempt($this->getAppContainer());

                // mark the job as completed
                $this->queue->complete($job);

                // increment completed jobs counter
                $this->completed++;

            } catch (Exception $e) {
                // check if we should release the item back on to the queue or mark it as failed
                if ($job->getAttempt() >= $this->maxAttempts) {
                    // mark as failed
                    $this->queue->fail($job);

                    // create the exception
                    $exception = new JobMaxAttemptsReachedException(
                        'Failed after ' . $this->maxAttempts . ' attempts', $e->getCode(), $e
                    );
                } else {
                    // add the job back on to the queue
                    $this->queue->release($job);

                    // create the exception
                    $exception = new JobException(
                        $e->getMessage(), $e->getCode(), $e
                    );
                }

                // increment failed counter
                $this->failed++;

                // throw max failed attempts
                $exception->setQueue($this->queue);
                $exception->setJob($job);
                throw $exception;
            }
        }

        return $job;
    }
}
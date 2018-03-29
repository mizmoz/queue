<?php

namespace Mizmoz\Queue;

use Exception;
use Mizmoz\Queue\Contract\HandleFailedJobInterface;
use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Contract\PayloadInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Contract\QueueProcessInterface;
use Mizmoz\Queue\Exception\JobException;
use Mizmoz\Queue\Exception\JobMaxAttemptsReachedException;
use Mizmoz\Queue\Exception\QueueNotFoundException;
use Psr\Container\ContainerInterface;

class Manager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var QueueInterface[]
     */
    private $queues = [];

    /**
     * @var HandleFailedJobInterface[]
     */
    private $failedJobHandlers = [];

    /**
     * Manager constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Set the failed job handlers
     *
     * @param HandleFailedJobInterface $handleFailedJob
     * @return Manager
     */
    public function setFailedJobHandler(HandleFailedJobInterface $handleFailedJob): Manager
    {
        $this->failedJobHandlers[] = $handleFailedJob;
        return $this;
    }

    /**
     * Add a queue to the manager
     *
     * @param string $name
     * @param QueueInterface $queue
     * @return Queue
     */
    public function addQueue(string $name, QueueInterface $queue): Queue
    {
        $this->queues[$name] = $queue;
        return new Queue($name, $queue, $this);
    }

    /**
     * Queue the job on the queue
     *
     * @param string $name
     * @param PayloadInterface $payload
     * @param int $delay Delay in milliseconds for the job execution
     * @return JobInterface
     */
    public function queue(string $name, PayloadInterface $payload, int $delay = 0): JobInterface
    {
        $job = new Job($payload);

        $this->getQueue($name)
            ->push($job, $delay);

        return $job;
    }

    /**
     * Get the queue
     *
     * @param string $name
     * @return QueueInterface
     */
    public function getQueue(string $name): QueueInterface
    {
        if (! $this->queueExists($name)) {
            throw new QueueNotFoundException('Queue "' . $name . '" not found');
        }

        return $this->queues[$name];
    }

    /**
     * Check if the queue exists
     *
     * @param string $name
     * @return bool
     */
    public function queueExists(string $name): bool
    {
        return isset($this->queues[$name]);
    }

    /**
     * Process the queue
     *
     * @param string $name
     * @param int $maxAttempts
     * @param int $maxMemory Max memory in Mb to use before quitting
     * @param int $waitInterval Interval between polling after the job queue is cleared
     * @param int $maxJobs Max number of jobs to process
     */
    public function process(
        string $name,
        int $maxAttempts = QueueProcessInterface::DEFAULT_MAX_ATTEMPTS,
        int $maxMemory = QueueProcessInterface::DEFAULT_MAX_MEMORY,
        int $waitInterval = QueueProcessInterface::DEFAULT_WAIT_INTERVAL,
        int $maxJobs = 0
    ) {
        $queue = $this->getQueue($name);
        $processor = new Processor($queue, $maxAttempts, $maxMemory, $waitInterval, $maxJobs);
        $this->processHandler($processor);
    }

    /**
     * Process a single item in the queue using pop();
     *
     * @param string $name
     * @param int $maxAttempts
     */
    public function processOne(string $name, int $maxAttempts = QueueProcessInterface::DEFAULT_MAX_ATTEMPTS)
    {
        $queue = $this->getQueue($name);
        $processor = new Processor($queue, $maxAttempts);
        $this->processHandler($processor, true);
    }

    /**
     * @param Processor $processor
     * @param bool $justOne
     */
    private function processHandler(Processor $processor, bool $justOne = false)
    {
        // set the app container
        $processor->setAppContainer($this->container);

        try {
            $processor->process($justOne);
        } catch (JobException $e) {
            // handle failed jobs
            foreach ($this->failedJobHandlers as $failedJobHandler) {
                $failedJobHandler->handle($e);
            }

            // call again
            $this->processHandler($processor, $justOne);
        } catch (Exception $e) {
            var_dump('Unhandled Exception', $e->getMessage(), $e->getTraceAsString());
            exit;
        }
    }
}
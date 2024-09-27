<?php

namespace Mizmoz\Queue;

use Mizmoz\Queue\Contract\DummyAdapterInterface;
use Mizmoz\Queue\Contract\PayloadInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Contract\QueueProcessInterface;
use Mizmoz\Queue\Contract\QueuePushInterface;

class Queue implements QueuePushInterface, QueueProcessInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var QueueInterface
     */
    private QueueInterface $queue;

    /**
     * @var Manager
     */
    private Manager $manager;

    /**
     * Queue constructor.
     *
     * @param string $name
     * @param QueueInterface $queue
     * @param Manager $manager
     */
    public function __construct(string $name, QueueInterface $queue, Manager $manager)
    {
        $this->name = $name;
        $this->queue = $queue;
        $this->manager = $manager;
    }

    /**
     * @inheritdoc
     */
    public function push(PayloadInterface $job, int $delay = 0): bool
    {
        $response = $this->queue->push(new Job($job), $delay);

        if ($this->queue instanceof DummyAdapterInterface) {
            // instantly process dummy adapter jobs
            $this->processOne();
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function process(int $maxAttempts = 1, int $maxMemory = 96, int $waitInterval = 5, int $maxJobs = 0): void
    {
        $this->manager->process($this->name, $maxAttempts, $maxMemory, $waitInterval, $maxJobs);
    }

    /**
     * @inheritdoc
     */
    public function processOne(int $maxAttempts = 1): void
    {
        $this->manager->processOne($this->name, $maxAttempts);
    }
}
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
    private $name;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var Manager
     */
    private $manager;

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
    public function push(PayloadInterface $payload, int $delay = 0): bool
    {
        $response = $this->queue->push(new Job($payload), $delay);

        if ($this->queue instanceof DummyAdapterInterface) {
            // instantly process dummy adapter jobs
            $this->processOne();
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function process(int $maxAttempts = 1, int $maxMemory = 96, int $waitInterval = 5, int $maxJobs = 0)
    {
        $this->manager->process($this->name, $maxAttempts, $maxMemory, $waitInterval, $maxJobs);
    }

    /**
     * @inheritdoc
     */
    public function processOne(int $maxAttempts = 1)
    {
        $this->manager->processOne($this->name, $maxAttempts);
    }
}
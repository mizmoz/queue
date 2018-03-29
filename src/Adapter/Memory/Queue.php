<?php

namespace Mizmoz\Queue\Adapter\Memory;

use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Exception\NotImplementedException;
use Mizmoz\Queue\Exception\QueueIsEmptyException;
use Mizmoz\Queue\Exception\QueueNotFoundException;
use SplQueue;

class Queue implements QueueInterface
{
    /**
     * @var SplQueue
     */
    private $queue;

    /**
     * Queue constructor.
     * @param SplQueue|null $queue
     */
    public function __construct(SplQueue $queue = null)
    {
        $this->queue = ($queue ? $queue : new SplQueue());
    }

    /**
     * Get the queue
     *
     * @return SplQueue
     */
    private function getQueue(): SplQueue
    {
        if (! $this->queue) {
            throw new QueueNotFoundException();
        }

        return $this->queue;
    }

    /**
     * @inheritDoc
     */
    public function complete(JobInterface $job): bool
    {
        // nothing to do for Spl as it dequeues with pop
        return true;
    }

    /**
     * @inheritDoc
     */
    public function fail(JobInterface $job): bool
    {
        // nothing to do for failed jobs here
        return true;
    }

    /**
     * @inheritDoc
     */
    public function pop(): JobInterface
    {
        if (! $this->getQueue()->count()) {
            throw new QueueIsEmptyException();
        }

        return $this->getQueue()->dequeue();
    }

    /**
     * @inheritDoc
     */
    public function push(JobInterface $job, int $delay = 0): bool
    {
        $this->getQueue()->enqueue($job);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function watch(int $waitInterval = 5): JobInterface
    {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function release(JobInterface $job): bool
    {
        return $this->push($job);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->getQueue()->count();
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        unset($this->queue);
        return true;
    }
}
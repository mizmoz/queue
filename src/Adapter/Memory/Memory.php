<?php

namespace Mizmoz\Queue\Adapter\Memory;

use Mizmoz\Queue\Contract\AdapterInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Exception\QueueAlreadyExistsException;
use Mizmoz\Queue\Exception\QueueNotFoundException;

class Memory implements AdapterInterface
{
    /**
     * @var Queue[]
     */
    protected $queues = [];

    /**
     * @inheritDoc
     */
    public function create(string $name): QueueInterface
    {
        if ($this->exists($name)) {
            throw new QueueAlreadyExistsException('Queue "' . $name . '" already exists');
        }

        return $this->queues[$name] = new Queue();
    }

    /**
     * @inheritDoc
     */
    public function exists(string $name): bool
    {
        return isset($this->queues[$name]);
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        return $this->queues;
    }

    /**
     * @inheritDoc
     */
    public function using(string $name): QueueInterface
    {
        if (! $this->exists($name)) {
            throw new QueueNotFoundException('Queue "' . $name . '" not found');
        }

        return $this->queues[$name];
    }
}

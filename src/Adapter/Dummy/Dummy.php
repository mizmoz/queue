<?php

namespace Mizmoz\Queue\Adapter\Dummy;

use Mizmoz\Queue\Adapter\Memory\Memory;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Exception\QueueAlreadyExistsException;

class Dummy extends Memory
{
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
}

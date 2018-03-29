<?php

namespace Mizmoz\Queue\Contract;

use Mizmoz\Queue\Exception\QueueAlreadyExistsException;
use Mizmoz\Queue\Exception\QueueNotFoundException;

interface AdapterInterface
{
    /**
     * Create a new queue
     *
     * @param string $name
     * @return QueueInterface
     * @throws QueueAlreadyExistsException
     */
    public function create(string $name): QueueInterface;

    /**
     * Check if a queue exists
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool;

    /**
     * Get a list of the queues
     *
     * @return array
     */
    public function get(): array;

    /**
     * Select the queue to use
     *
     * @param string $name
     * @return QueueInterface
     * @throws QueueNotFoundException
     */
    public function using(string $name): QueueInterface;
}
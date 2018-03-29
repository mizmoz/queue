<?php

namespace Mizmoz\Queue\Contract;

interface QueueAccessorInterface
{
    /**
     * Get the queue
     *
     * @return QueueInterface
     */
    public function getQueue(): QueueInterface;

    /**
     * Set the queue
     *
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue);
}
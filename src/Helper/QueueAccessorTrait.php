<?php

namespace Mizmoz\Queue\Helper;

use Mizmoz\Queue\Contract\QueueInterface;

trait QueueAccessorTrait
{
    private $queueInstance;

    /**
     * @inheritdoc
     */
    public function getQueue(): QueueInterface
    {
        return $this->queueInstance;
    }

    /**
     * @inheritdoc
     */
    public function setQueue(QueueInterface $queue)
    {
        $this->queueInstance = $queue;
    }
}
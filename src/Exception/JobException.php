<?php

namespace Mizmoz\Queue\Exception;

use Mizmoz\Queue\Contract\JobAccessorInterface;
use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Contract\QueueAccessorInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Helper\JobAccessorTrait;
use Mizmoz\Queue\Helper\QueueAccessorTrait;
use RuntimeException;

class JobException extends RuntimeException implements JobAccessorInterface, QueueAccessorInterface
{
    /**
     * @var bool
     */
    protected bool $fatal = false;

    /**
     * @var JobInterface|null
     */
    private ?JobInterface $jobInstance;

    /**
     * @var QueueInterface|null
     */
    private ?QueueInterface $queueInstance;

    /**
     * @inheritdoc
     */
    public function getJob(): ?JobInterface
    {
        return $this->jobInstance;
    }

    /**
     * @inheritdoc
     */
    public function getQueue(): ?QueueInterface
    {
        return $this->queueInstance;
    }

    /**
     * Was the error fatal?
     *
     * @return bool
     */
    public function isFatal(): bool
    {
        return $this->fatal;
    }

    /**
     * Is this exception fatal meaning the job cannot continue to be run and will not be re-queued?
     *
     * @param bool $fatal
     * @return JobException
     */
    public function setFatal(bool $fatal = true): JobException
    {
        $this->fatal = $fatal;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setJob(JobInterface $job): void
    {
        $this->jobInstance = $job;
    }

    /**
     * @inheritdoc
     */
    public function setQueue(QueueInterface $queue): void
    {
        $this->queueInstance = $queue;
    }
}

<?php

namespace Mizmoz\Queue\Helper;

use Mizmoz\Queue\Contract\JobInterface;

trait JobAccessorTrait
{
    private $jobInstance;

    /**
     * @inheritdoc
     */
    public function getJob(): JobInterface
    {
        return $this->jobInstance;
    }

    /**
     * @inheritdoc
     */
    public function setJob(JobInterface $job)
    {
        $this->jobInstance = $job;
    }
}
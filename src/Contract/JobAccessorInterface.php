<?php

namespace Mizmoz\Queue\Contract;

interface JobAccessorInterface
{
    /**
     * Get the job
     *
     * @return JobInterface
     */
    public function getJob(): JobInterface;

    /**
     * Set the job
     *
     * @param JobInterface $job
     */
    public function setJob(JobInterface $job);
}
<?php


namespace Mizmoz\Queue\Contract;

use Mizmoz\Queue\Exception\JobException;

interface HandleFailedJobInterface
{
    /**
     * Handle the failed job
     *
     * @param JobException $exception
     */
    public function handle(JobException $exception);
}
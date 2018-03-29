<?php

namespace Mizmoz\Queue\Exception;

use Mizmoz\Queue\Contract\JobAccessorInterface;
use Mizmoz\Queue\Contract\QueueAccessorInterface;
use Mizmoz\Queue\Helper\JobAccessorTrait;
use Mizmoz\Queue\Helper\QueueAccessorTrait;
use RuntimeException;

class JobException extends RuntimeException implements JobAccessorInterface, QueueAccessorInterface
{
    use JobAccessorTrait;
    use QueueAccessorTrait;

    /**
     * @var bool
     */
    protected $fatal = false;

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
     * Was the error fatal?
     *
     * @return bool
     */
    public function isFatal(): bool
    {
        return $this->fatal;
    }
}

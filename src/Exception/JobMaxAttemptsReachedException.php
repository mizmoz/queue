<?php

namespace Mizmoz\Queue\Exception;

class JobMaxAttemptsReachedException extends JobException
{
    /**
     * @var bool
     */
    protected bool $fatal = true;
}

<?php

namespace Mizmoz\Queue\Tests\Job;

use Mizmoz\Queue\Contract\PayloadInterface;

class TestPayload implements PayloadInterface
{
    /**
     * @var int
     */
    public int $number;

    /**
     * Init with the number
     *
     * @param int $number
     */
    public function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * @inheritDoc
     */
    public function execute(): int
    {
        // very important job to execute...
        return $this->number * 100;
    }
}
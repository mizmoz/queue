<?php

namespace Mizmoz\Queue\Contract;

interface PayloadInterface
{
    /**
     * Execute the payload
     *
     * @return mixed
     */
    public function execute(): mixed;
}
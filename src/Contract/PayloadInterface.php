<?php

namespace Mizmoz\Queue\Contract;

interface PayloadInterface
{
    /**
     * Execute the payload
     */
    public function execute();
}
<?php

namespace Mizmoz\Queue\Tests\Cli;

use Mizmoz\Container\Container;
use Mizmoz\Queue\Adapter\Dummy\Dummy;
use Mizmoz\Queue\Manager;
use Mizmoz\Queue\Tests\Job\TestPayload;
use Mizmoz\Queue\Tests\TestCase;
use Mockery;

class ProcessFactory extends TestCase
{
    public function testCreateProcess()
    {
        $process = \Mizmoz\Queue\Cli\ProcessFactory::build(new Container(), new Manager(new Dummy()));
    }
}
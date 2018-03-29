<?php

namespace Mizmoz\Queue\Tests\Adapter\Dummy;

use Mizmoz\Container\Container;
use Mizmoz\Queue\Adapter\Dummy\Dummy;
use Mizmoz\Queue\Manager;
use Mizmoz\Queue\Tests\Job\TestPayload;
use Mizmoz\Queue\Tests\TestCase;
use Mockery;

class DummyTest extends TestCase
{
    public function testCreateQueue()
    {
        $manager = new Manager(new Container());
        $queue = $manager->addQueue('dummy', (new Dummy())->create('dummy'));

        $payload = Mockery::mock(TestPayload::class);
        $payload->shouldReceive('execute')
            ->once();

        $queue->push($payload);
    }
}
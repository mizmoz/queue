<?php

namespace Mizmoz\Queue\Tests;

use Mizmoz\Container\Container;
use Mockery;
use Mizmoz\Queue\Contract\PayloadInterface;
use Mizmoz\Queue\Job;
use Mizmoz\Queue\Tests\Job\TestPayload;

class JobTest extends TestCase
{
    public function testJobCreation()
    {
        $payload = new TestPayload(123);
        $job = new Job($payload);

        // check we can get the payload
        $this->assertEquals($payload, $job->getPayload());

        // check we create a message
        $this->assertInternalType('string', $job->getMessage());
    }

    public function testJobInstantiation()
    {
        // make sure we can instantiate the job without constructor arguments
        $this->assertInstanceOf(Job::class, new Job());
    }

    public function testGetSetMessagePayload()
    {
        $job = new Job(new TestPayload(123));

        // get the message
        $message = $job->getMessage();

        // make sure we get a string
        $this->assertInternalType('string', $message);

        // now set the message
        $this->assertTrue($job->setMessage($message));

        // check we get a payload
        $this->assertInstanceOf(PayloadInterface::class, $job->getPayload());

        // check the attempts count
        $this->assertEquals(0, $job->getAttempt());
    }

    public function testAttemptJob()
    {
        $payload = Mockery::mock(TestPayload::class);
        $payload->shouldReceive('execute')
            ->once();

        $job = new Job($payload);
        $job->attempt(new Container());

        // we should have an incremented counter
        $this->assertEquals(1, $job->getAttempt());
    }

    public function testJobAttemptsCount()
    {
        // make sure the counter is kept through getting and setting the message
        $job = new Job(new TestPayload(123));

        // increment the counter
        $job->attempt(new Container());

        // check we're at one now...
        $this->assertEquals(1, $job->getAttempt());

        // get the message and create a new job with it
        $job2 = new Job();
        $job2->setMessage($job->getMessage());

        // check we're at one now...
        $this->assertEquals(1, $job2->getAttempt());

        // increment the counter
        $job2->attempt(new Container());

        // check we're at one now...
        $this->assertEquals(2, $job2->getAttempt());
    }
}
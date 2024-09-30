<?php

namespace Mizmoz\Queue\Tests\Adapter\Beanstalk;

use Mizmoz\Container\Container;
use Mizmoz\Queue\Adapter\Beanstalk\Beanstalk;
use Mizmoz\Queue\Adapter\Beanstalk\Queue;
use Mizmoz\Queue\Contract\AdapterInterface;
use Mizmoz\Queue\Job;
use Mizmoz\Queue\Processor;
use Mizmoz\Queue\Tests\Job\TestPayload;
use Mizmoz\Queue\Tests\TestCase;
use Pheanstalk\Pheanstalk;

class QueueTest extends TestCase
{
    /**
     * @var Queue|null
     */
    private ?Queue $queue;

    protected function setUp(): void
    {
        parent::setUp();
        $pheanstalk = Pheanstalk::create('dev.mizmoz.com');
        $this->queue = new Queue("mizmoz-queue-test", $pheanstalk);
        $this->queue->delete();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->queue?->delete();
    }

    public function testPushJobOnToQueue(): void
    {
        $this->queue->push(new Job(new TestPayload(101)));
        $this->assertSame(1, $this->queue->count());
    }

    public function testDeletingTheQueue(): void
    {
        $this->queue->push(new Job(new TestPayload(101)));
        $this->queue->delete();
        $this->assertSame(0, $this->queue->count());
    }

    public function testPopJobFromQueue(): void
    {
        $this->queue->push(new Job(new TestPayload(123)));
        $this->assertSame(1, $this->queue->count());
        $job = $this->queue->pop();

        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals(0, $job->getAttempt());

        $this->assertTrue($job->attempt(new Container()));

        $this->queue->complete($job);

        $this->assertSame(0, $this->queue->count());
    }

    public function testMarkingJobAsFailed(): void
    {
        $this->queue->push(new Job(new TestPayload(123)));
        $this->assertSame(1, $this->queue->count());
        $job = $this->queue->pop();
        $this->queue->fail($job);
        $this->assertSame(0, $this->queue->count());
    }
}


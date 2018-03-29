<?php

namespace Mizmoz\Queue\Tests;

use Mizmoz\Container\Container;
use Mizmoz\Queue\Contract\HandleFailedJobInterface;
use Mockery;
use Mizmoz\Queue\Adapter\Memory\Memory;
use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Manager;
use Mizmoz\Queue\Tests\Job\TestPayload;

class ManagerTest extends TestCase
{
    /**
     * Create the test manager with the queue name
     *
     * @param string $name
     * @return Manager
     */
    private function getTestManager(string $name = 'test'): Manager
    {
        $manager = new Manager(new Container());
        $manager->addQueue($name, (new Memory())->create($name));
        return $manager;
    }

    public function testAddItemToQueue()
    {
        // create the test queue manager
        $manager = $this->getTestManager('test');

        // add the job to the queue
        $job = $manager->queue('test', new TestPayload(54));
        $this->assertInstanceOf(JobInterface::class, $job);
        $this->assertEquals(0, $job->getAttempt());

        // get the queue length
        $this->assertEquals(1, $manager->getQueue('test')->count());
    }

    public function testWorkQueue()
    {
        // create the test queue manager
        $manager = $this->getTestManager('test');

        // create the mock payload
        $payload = Mockery::mock(TestPayload::class);
        $payload->shouldReceive('execute')
            ->andReturn(true)
            ->once();

        // add a job to the queue
        $manager->queue('test', $payload);

        // work the queue
        $manager->processOne('test');

        // check the queue is empty now
        $this->assertEquals(0, $manager->getQueue('test')->count());
    }

    public function testWorkQueueFail()
    {
        // create the test queue manager
        $manager = $this->getTestManager('test');

        // create the mock payload
        $payload = Mockery::mock(TestPayload::class);
        $payload->shouldReceive('execute')
            ->andThrow(\RuntimeException::class, 'Something went wrong')
            ->once();

        // add a job to the queue
        $manager->queue('test', $payload);

        // work the queue
        $manager->processOne('test', 2);

        // check the queue still has an item in it
        $this->assertEquals(1, $manager->getQueue('test')->count());
    }

    public function testWorkQueueFailHandler()
    {
        // create the test queue manager
        $manager = $this->getTestManager('test');

        // create the handler
        $handler = Mockery::mock(HandleFailedJobInterface::class);
        $handler->shouldReceive('handle')
            ->once();

        $manager->setFailedJobHandler($handler);

        // create the mock payload
        $payload = Mockery::mock(TestPayload::class);
        $payload->shouldReceive('execute')
            ->andThrow(\RuntimeException::class, 'Something went wrong')
            ->once();

        // add a job to the queue
        $manager->queue('test', $payload);

        // work the queue
        $manager->processOne('test');

        // check the queue is now empty
        $this->assertEquals(0, $manager->getQueue('test')->count());
    }
}
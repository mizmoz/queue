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

class BeanstalkTest extends TestCase
{
    private function getBeanstalk(): Beanstalk
    {
        $pheanstalk = new Pheanstalk('dev.mizmoz.com');
        return new Beanstalk($pheanstalk);
    }

    public function testInit(): void
    {
        $beanstalk = $this->getBeanstalk();
        $this->assertInstanceOf(AdapterInterface::class, $beanstalk);
    }

    public function testGetQueues(): void
    {
        $beanstalk = $this->getBeanstalk();
        $this->assertContains('default', $beanstalk->get());
    }

    public function testQueueCreationOnUsing(): void
    {
        $beanstalk = $this->getBeanstalk();
        $this->assertInstanceOf(Queue::class, $beanstalk->using('test1'));
        $this->assertInstanceOf(Queue::class, $beanstalk->using('test2'));
        $this->assertInstanceOf(Queue::class, $beanstalk->using('test3'));
    }

    public function testQueueAlwaysExists(): void
    {
        $beanstalk = $this->getBeanstalk();
        $this->assertTrue($beanstalk->exists('test101'));
        $this->assertTrue($beanstalk->exists('test202'));
        $this->assertTrue($beanstalk->exists('test303'));
    }

    public function testCreateJobOnQueue(): void
    {
        $beanstalk = $this->getBeanstalk();

        $job = new Job(new TestPayload(123));

        // should be OK to add job
        $this->assertTrue($beanstalk->create('test')->push($job));

        // check the queue length
        $this->assertEquals(1, $beanstalk->using('test')->count());

        // process the job
        (new Processor($beanstalk->using('test')))
            ->setAppContainer(new Container())
            ->process(true);

        // check the queue length
        $this->assertEquals(0, $beanstalk->using('test')->count());
    }
}
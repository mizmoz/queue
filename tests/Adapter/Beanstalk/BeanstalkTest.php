<?php

namespace Mizmoz\Queue\Tests\Adapter\Beanstalk;

use Mizmoz\Container\Container;
use Mizmoz\Queue\Adapter\Beanstalk\Beanstalk;
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

    public function testInit()
    {
        $beanstalk = $this->getBeanstalk();
        $this->assertInstanceOf(AdapterInterface::class, $beanstalk);
    }

    public function testGetQueues()
    {
        $beanstalk = $this->getBeanstalk();
        $this->assertArraySubset(['default'], $beanstalk->get());
    }

    public function testCreateJobOnQueue()
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
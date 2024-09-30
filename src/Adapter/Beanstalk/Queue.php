<?php

namespace Mizmoz\Queue\Adapter\Beanstalk;

use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Exception\QueueIsEmptyException;
use Mizmoz\Queue\Job;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\Job as PheanstalkJob;
use Pheanstalk\Values\JobId;
use Pheanstalk\Values\JobStats;
use Pheanstalk\Values\TubeName;
use Pheanstalk\Values\TubeStats;

class Queue implements QueueInterface
{
    /**
     * @var TubeName $tubeName
     */
    private TubeName $tubeName;

    /**
     * @var Pheanstalk
     */
    private Pheanstalk $connection;

    /**
     * @var int
     */
    private int $ttr;

    /**
     * Queue constructor.
     * @param string $name
     * @param Pheanstalk $pheanstalk
     * @param int $ttr Time to run job before it will be released back on to the queue
     */
    public function __construct(
        string $name,
        Pheanstalk $pheanstalk,
        int $ttr = PheanstalkPublisherInterface::DEFAULT_TTR
    ) {
        $this->tubeName = new TubeName($name);
        $this->connection = $pheanstalk;
        $this->ttr = $ttr;

        $this->connection->watch($this->tubeName);
    }

    /**
     * Get the Job
     *
     * @param PheanstalkJob $pheanstalkJob
     * @return JobInterface
     */
    private function getJob(PheanstalkJob $pheanstalkJob): JobInterface
    {
        $job = new Job();
        $job->setId($pheanstalkJob->getId());
        $job->setMessage($pheanstalkJob->getData());
        $job->setAttempt($this->getJobStats($pheanstalkJob)->releases);
        return $job;
    }

    /**
     * @inheritDoc
     */
    public function complete(JobInterface $job): bool
    {
        $id = new JobId($job->getId());
        $this->connection->delete($id);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function fail(JobInterface $job): bool
    {
        return $this->complete($job);
    }

    /**
     * @inheritDoc
     */
    public function push(JobInterface $job, int $delay = 0): bool
    {
        $this->connection->useTube($this->tubeName);
        $id = $this->connection->put($job->getMessage(), PheanstalkPublisherInterface::DEFAULT_PRIORITY, $delay, $this->ttr)->getId();

        if ($id) {
            $job->setId($id);
        }

        return (bool)$id;
    }

    /**
     * @inheritDoc
     */
    public function pop(): JobInterface
    {
        $response = $this->connection->reserve();
        return $this->getJob($response);
    }

    /**
     * @inheritDoc
     */
    public function watch(int $waitInterval = 5): JobInterface
    {

        $job = $this->connection->reserve();
        return $this->getJob($job);
    }

    /**
     * @inheritDoc
     */
    public function release(JobInterface $job): bool
    {
        // delete the job from the queue
        $id = new JobId($job->getId());
        $this->connection->release($id);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->getStats()->currentJobsReady;
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        while ($this->count()) {
             $this->complete($this->pop());
        }
        return true;
    }

    /**
     * Get the stats for the queue
     *
     * @return TubeStats
     */
    private function getStats(): TubeStats
    {
        return $this->connection->statsTube($this->tubeName);
    }

    /**
     * Get the stats for the job
     *
     * @param JobIdInterface $id
     * @return JobStats
     */
    private function getJobStats(JobIdInterface $id): JobStats
    {
        return $this->connection->statsJob($id);
    }
}
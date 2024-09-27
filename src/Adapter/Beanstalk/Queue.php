<?php

namespace Mizmoz\Queue\Adapter\Beanstalk;

use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Mizmoz\Queue\Exception\QueueIsEmptyException;
use Mizmoz\Queue\Job;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Response\ArrayResponse;

class Queue implements QueueInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var PheanstalkInterface
     */
    private PheanstalkInterface $connection;

    /**
     * @var int
     */
    private int $ttr;

    /**
     * Queue constructor.
     * @param string $name
     * @param PheanstalkInterface $pheanstalk
     * @param int $ttr Time to run job before it will be released back on to the queue
     */
    public function __construct(
        string $name,
        PheanstalkInterface $pheanstalk,
        int $ttr = PheanstalkInterface::DEFAULT_TTR
    ) {
        $this->name = $name;
        $this->connection = $pheanstalk;
        $this->ttr = $ttr;
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
        $job->setId((string)$pheanstalkJob->getId());
        $job->setMessage($pheanstalkJob->getData());
        $job->setAttempt((int)$this->getJobStats($job->getId())['releases']);
        return $job;
    }

    /**
     * Get the Job
     *
     * @param JobInterface $job
     * @return PheanstalkJob
     */
    private function getPheanstalkJob(JobInterface $job): PheanstalkJob
    {
        return new PheanstalkJob((int)$job->getId(), '');
    }

    /**
     * @inheritDoc
     */
    public function complete(JobInterface $job): bool
    {
        $this->connection->delete($this->getPheanstalkJob($job));
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
        $id = $this->connection
            ->putInTube($this->name, $job->getMessage(), PheanstalkInterface::DEFAULT_PRIORITY, $delay, $this->ttr);

        if ($id) {
            $job->setId((string)$id);
        }

        return (bool)$id;
    }

    /**
     * @inheritDoc
     */
    public function pop(): JobInterface
    {
        /** @var PheanstalkJob $response */
        $response = $this->connection->reserveFromTube($this->name, 0);

        if (! $response) {
            throw new QueueIsEmptyException();
        }

        return $this->getJob($response);
    }

    /**
     * @inheritDoc
     */
    public function watch(int $waitInterval = 5): JobInterface
    {
        /** @var PheanstalkJob $job */
        $job = $this->connection->watchOnly($this->name)->reserve($waitInterval);

        if (! $job) {
            throw new QueueIsEmptyException();
        }

        return $this->getJob($job);
    }

    /**
     * @inheritDoc
     */
    public function release(JobInterface $job): bool
    {
        // delete the job from the queue
        $this->connection->release($this->getPheanstalkJob($job));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return (int)$this->getStats()['current-jobs-ready'];
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        try {
            while ($job = $this->pop()) {
                $this->complete($job);
            }
        } catch (QueueIsEmptyException $e) {
            // fall through to return true
        }

        return true;
    }

    /**
     * Get the stats for the queue
     *
     * @return ArrayResponse
     */
    private function getStats(): ArrayResponse
    {
        return $this->connection->statsTube($this->name);
    }

    /**
     * Get the stats for the job
     *
     * @return ArrayResponse
     */
    private function getJobStats(string $id): ArrayResponse
    {
        return $this->connection->statsJob((int)$id);
    }
}
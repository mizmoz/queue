<?php

namespace Mizmoz\Queue\Adapter\Beanstalk;

use Mizmoz\Queue\Contract\AdapterInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Pheanstalk\Contract\PheanstalkPublisherInterface;
use Pheanstalk\Pheanstalk;

class Beanstalk implements AdapterInterface
{
    /**
     * @var Pheanstalk
     */
    private Pheanstalk $connection;

    /**
     * @var QueueInterface[]
     */
    private array $queues = [];

    /**
     * @var int
     */
    private int $ttr;

    /**
     * Beanstalk constructor.
     * @param Pheanstalk $pheanstalk
     * @param int $ttr Time to run job before it's released back on to the queue
     */
    public function __construct(Pheanstalk $pheanstalk, int $ttr = PheanstalkPublisherInterface::DEFAULT_TTR) {
        $this->connection = $pheanstalk;
        $this->ttr = $ttr;
    }

    /**
     * Get teh queue
     *
     * @param string $name
     * @return QueueInterface
     */
    private function getQueue(string $name): QueueInterface
    {
        if (! isset($this->queues[$name])) {
            $this->create($name);
        }

        // return the queue
        return $this->queues[$name];
    }

    /**
     * @inheritDoc
     */
    public function create(string $name): QueueInterface
    {
        // Beanstalk doesn't require tubes to be created so we'll just set the current
        return $this->queues[$name] = new Queue($name, $this->connection, $this->ttr);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $name): bool
    {
        // always return true as queues are created at will with beanstalk
        return true;
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        foreach ($this->connection->listTubes() as $tube) {
            $this->create($tube);
        }
        return $this->queues;
    }

    /**
     * @inheritDoc
     */
    public function using(string $name): QueueInterface
    {
        return $this->getQueue($name);
    }
}
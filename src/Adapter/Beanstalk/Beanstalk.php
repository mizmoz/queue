<?php

namespace Mizmoz\Queue\Adapter\Beanstalk;

use Mizmoz\Queue\Contract\AdapterInterface;
use Mizmoz\Queue\Contract\QueueInterface;
use Pheanstalk\PheanstalkInterface;

class Beanstalk implements AdapterInterface
{
    /**
     * @var PheanstalkInterface
     */
    private $connection;

    /**
     * @var QueueInterface[]
     */
    private $queues = [];

    /**
     * @var int
     */
    private $ttr = 60;

    /**
     * Beanstalk constructor.
     * @param PheanstalkInterface $pheanstalk
     * @param int $ttr Time to run job before it's released back on to the queue
     */
    public function __construct(PheanstalkInterface $pheanstalk, int $ttr = PheanstalkInterface::DEFAULT_TTR) {
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
        return $this->connection->listTubes();
    }

    /**
     * @inheritDoc
     */
    public function using(string $name): QueueInterface
    {
        return $this->getQueue($name);
    }
}
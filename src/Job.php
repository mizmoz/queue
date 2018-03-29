<?php

namespace Mizmoz\Queue;

use Exception;
use Mizmoz\Container\InjectContainer;
use Mizmoz\Queue\Contract\JobInterface;
use Mizmoz\Queue\Contract\PayloadInterface;
use Psr\Container\ContainerInterface;

class Job implements Contract\JobInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var PayloadInterface
     */
    private $payload;

    /**
     * @var bool
     */
    private $compress = false;

    /**
     * @var int
     */
    private $attempt = 0;

    /**
     * Manage the attempts internally
     *
     * @var bool
     */
    private $manageAttempts = true;

    /**
     * Job constructor.
     *
     * @param PayloadInterface $payload
     */
    public function __construct(PayloadInterface $payload = null)
    {
        $this->payload = $payload;
    }

    /**
     * Attempt to compress the payload?
     *
     * @param bool $compress
     * @return Job
     */
    public function compress(bool $compress): Job
    {
        $this->compress = $compress;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function attempt(ContainerInterface $container): bool
    {
        if ($this->manageAttempts) {
            $this->attempt++;
        }

        // inject the container and execute the payload
        InjectContainer::inject($container, $this->getPayload())->execute();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): JobInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAttempt(): int
    {
        return $this->attempt;
    }

    /**
     * @inheritDoc
     */
    public function setAttempt(int $attempt): JobInterface
    {
        // stop managing the attempts internally
        $this->manageAttempts = false;
        $this->attempt = $attempt;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return json_encode([
            'attempt' => $this->attempt,
            'payload' => serialize($this->payload),
            'compress' => $this->compress,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }

    /**
     * @inheritDoc
     */
    public function setMessage(string $message): bool
    {
        if (! $data = json_decode($message, true)) {
            return false;
        }

        foreach ($data as $key => $value) {
            $this->{$key} = ($key === 'payload' ? unserialize($value) : $value);
        }

        return true;
    }
}
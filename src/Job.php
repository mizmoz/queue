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
    private string $id;

    /**
     * @var PayloadInterface|null
     */
    private mixed $payload;

    /**
     * @var bool
     */
    private bool $compress = false;

    /**
     * @var int
     */
    private int $attempt = 0;

    /**
     * Manage the attempts internally
     *
     * @var bool
     */
    private bool $manageAttempts = true;

    /**
     * Job constructor.
     *
     * @param PayloadInterface|null $payload
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
        $result = InjectContainer::inject($container, $this->getPayload())->execute();

        // if the result is null, assume the job was successful as it could throw
        // to indicate a failure, otherwise if there is a result then check it
        // is true ish.
        return is_null($result) || $result;
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
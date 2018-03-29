<?php

namespace Mizmoz\Queue\Contract;

use Psr\Container\ContainerInterface;

interface JobInterface
{
    /**
     * Return false if the job fails. This will release the job back on to the queue if the
     * max attempts hasn't been reached.
     *
     * @param ContainerInterface $container
     * @return bool
     */
    public function attempt(ContainerInterface $container): bool;

    /**
     * Return the number of attempts at processing this jobs so far
     *
     * @return int
     */
    public function getAttempt(): int;

    /**
     * Set attempt
     *
     * @param int $attempt
     * @return JobInterface
     */
    public function setAttempt(int $attempt): JobInterface;

    /**
     * Get the job ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set the job ID
     *
     * @param string $id
     * @return JobInterface
     */
    public function setId(string $id): JobInterface;

    /**
     * Get the job message for the queue
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Set the job message from the queue. It should return false if the message fails to set.
     *
     * @param string $message
     * @return bool
     */
    public function setMessage(string $message): bool;

    /**
     * Get the job payload
     *
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface;
}
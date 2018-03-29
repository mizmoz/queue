<?php

namespace Mizmoz\Queue;

use Mizmoz\Queue\Cli\ProcessFactory;
use Mizmoz\Queue\Contract\ListenerInterface;
use Mizmoz\Queue\Contract\QueueProcessInterface;
use Symfony\Component\Process\Process;

class Listener implements ListenerInterface
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var bool
     */
    private $shutdown = false;

    /**
     * Register the signal handlers
     */
    private function registerSigHandlers()
    {
        declare(ticks = 1);

        pcntl_signal(SIGINT, function () {
            // pass the signal on to the child process
            $this->shutdown = true;
            $this->process->signal(SIGINT);
        });

        pcntl_signal(SIGTERM, function () {
            // pass the signal on to the child process
            $this->shutdown = true;
            $this->process->signal(SIGTERM);
        });
    }

    /**
     * @inheritDoc
     */
    public function listen(
        string $queue = 'default',
        int $maxAttempts = QueueProcessInterface::DEFAULT_MAX_ATTEMPTS,
        int $maxMemory = QueueProcessInterface::DEFAULT_MAX_MEMORY,
        int $waitInterval = QueueProcessInterface::DEFAULT_WAIT_INTERVAL,
        int $maxJobs = 0
    ) {
        $this->process = ProcessFactory::process([
            'queue' => $queue,
            'maxAttempts' => $maxAttempts,
            'maxMemory' => $maxMemory,
            'waitInterval' => $waitInterval,
            'maxJobs' => $maxJobs,
        ]);

        // disable the output
        $this->process->disableOutput();

        // register the signal handlers
        $this->registerSigHandlers();

        while (! $this->shutdown) {
            // run the processor
            $this->process->start();

            while ($this->process->isRunning()) {
                // keep running, don't check shutdown flag as we want to allow the child process to stop
                sleep(1);
            }
        }
    }
}
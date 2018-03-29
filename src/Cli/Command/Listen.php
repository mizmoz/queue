<?php

namespace Mizmoz\Queue\Cli\Command;

use Mizmoz\Container\ManageContainerTrait;
use Mizmoz\Queue\Contract\QueueProcessInterface;
use Mizmoz\Queue\Listener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Listen extends Command
{
    use ManageContainerTrait;

    use ManageQueueTrait;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('queue:listen')
            ->setDescription('Listen and process jobs on the given queues')
            ->addOption(
                'queue', '',
                InputOption::VALUE_REQUIRED,
                'Queue name to listen on',
                'default'
            )->addOption(
                'maxAttempts', '',
                InputOption::VALUE_OPTIONAL,
                'Maximum attempts to process a job',
                QueueProcessInterface::DEFAULT_MAX_ATTEMPTS
            )->addOption(
                'maxJobs', '',
                InputOption::VALUE_OPTIONAL,
                'Maximum jobs to process',
                0
            )->addOption(
                'maxMemory', '',
                InputOption::VALUE_OPTIONAL,
                'Maximum memory to use before quitting',
                QueueProcessInterface::DEFAULT_MAX_MEMORY
            )->addOption(
                'waitInterval', '',
                InputOption::VALUE_OPTIONAL,
                'Wait timer between checking for new jobs',
                QueueProcessInterface::DEFAULT_WAIT_INTERVAL
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get the queue, if we can't resolve the queue this will fail
        $queue = $this->getQueue($input, $output);

        $output->writeln('Starting listener for: ' . $input->getOption('queue'));

        (new Listener())->listen(
            $input->getOption('queue'),
            (int)$input->getOption('maxAttempts'),
            (int)$input->getOption('maxMemory'),
            (int)$input->getOption('waitInterval'),
            (int)$input->getOption('maxJobs')
        );

        $output->writeln('Done listening');
    }
}
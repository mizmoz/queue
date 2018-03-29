<?php

namespace Mizmoz\Queue\Cli\Command;

use Mizmoz\Container\ManageContainerTrait;
use Mizmoz\Queue\Contract\QueueProcessInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Command
{
    use ManageContainerTrait;

    use ManageQueueTrait;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('queue:process')
            ->setDescription('Process the given jobs in the queue.')
            ->addOption(
                'queue', '', InputOption::VALUE_REQUIRED, 'Queue name to listen on', 'default'
            )->addOption(
                'maxAttempts', '', InputOption::VALUE_OPTIONAL, 'Maximum attempts to process a job', 3
            )->addOption(
                'maxJobs', '', InputOption::VALUE_OPTIONAL, 'Maximum jobs to process', 0
            )->addOption(
                'maxMemory', '', InputOption::VALUE_OPTIONAL, 'Maximum memory to use before quitting', 96
            )->addOption(
                'waitInterval', '', InputOption::VALUE_OPTIONAL, 'Wait timer between checking for new jobs', 5
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get the queue
        $queue = $this->getQueue($input, $output);

        $output->writeln('Processing queue: ' . $input->getOption('queue'));

        // run until we exhuast the allowed memory
        $queue->process(
            (int)$input->getOption('maxAttempts'),
            (int)$input->getOption('maxMemory'),
            (int)$input->getOption('waitInterval'),
            (int)$input->getOption('maxJobs')
        );

        $output->writeln('Killing process');
    }
}
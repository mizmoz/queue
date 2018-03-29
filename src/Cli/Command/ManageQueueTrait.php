<?php

namespace Mizmoz\Queue\Cli\Command;

use Mizmoz\Queue\Contract\QueueProcessInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ManageQueueTrait
{
    /**
     * Get the queue
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return QueueProcessInterface
     */
    private function getQueue(InputInterface $input, OutputInterface $output): QueueProcessInterface
    {
        // get the queue name
        $name = 'queue.' . $input->getOption('queue');

        // return the queue item
        return $this->getAppContainer()->get($name);
    }
}
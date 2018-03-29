<?php

namespace Mizmoz\Queue\Cli;

use Mizmoz\App\Contract\AppCliRegistrationInterface;
use Mizmoz\App\Contract\CliAppInterface;
use Mizmoz\Config\Contract\ConfigInterface;
use Mizmoz\Container\Contract\ContainerInterface;
use Mizmoz\Queue\Cli\Command\Job;
use Mizmoz\Queue\Cli\Command\Listen;
use Mizmoz\Queue\Cli\Command\Process;
use Mizmoz\Queue\Exception\QueueNotFoundException;

class AppRegistration implements AppCliRegistrationInterface
{
    /**
     * @inheritdoc
     */
    public function registerCli(CliAppInterface $app, ContainerInterface $container, ConfigInterface $config)
    {
        if (! $container->has('queue')) {
            // no queue has been set!
            throw new QueueNotFoundException('At least one queue must be set in the container');
        }

        $app->addCommand($container->get(Listen::class));
        $app->addCommand($container->get(Process::class));
    }
}
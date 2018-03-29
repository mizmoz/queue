<?php

namespace Mizmoz\Queue\Cli;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class ProcessFactory
{
    /**
     * Create the process
     *
     * @param array $arguments
     * @return Process
     */
    public static function process(array $arguments = []): Process
    {
        return static::build('queue:process', $arguments);
    }

    /**
     * Build the command
     *
     * @param string $command
     * @param array $arguments
     */
    /**
     * @param string $command
     * @param array $arguments
     * @return Process
     */
    public static function build(string $command, array $arguments = []): Process
    {
        $args = [];

        // create the process cli
        $args[] = (new PhpExecutableFinder())->find();

        // resolve the mizmoz executable
        $mizmoz = $_SERVER['PHP_SELF'];
        $args[] = (strpos($mizmoz, '/') === 0 ? $mizmoz : realpath(getcwd() . '/' . $mizmoz));

        // add the command
        $args[] = $command;

        // add the arguments
        foreach ($arguments as $key => $value) {
            $args[] = '--' . $key . '=' . $value;
        }

        // create the process command
        return (new ProcessBuilder($args))->getProcess();
    }
}
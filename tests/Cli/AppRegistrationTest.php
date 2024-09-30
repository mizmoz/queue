<?php

namespace Mizmoz\Queue\Tests\Cli;

use Mizmoz\App\Cli\App;
use Mizmoz\Queue\Adapter\Dummy\Dummy;
use Mizmoz\Queue\Cli\AppRegistration;
use Mizmoz\Queue\Tests\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class AppRegistrationTest extends TestCase
{
    public function testCliAppRegistration(): void
    {
        $app = App::create(__DIR__);
        $app->container()->add('queue', Dummy::class);
        $app->boot();
        $app->register(new AppRegistration());

        $input = new StringInput('queue:listen');
        $output = new BufferedOutput();
        $app->run($input, $output);

        // sure there must be a better way to test this...
        $this->assertStringContainsString("queue:listen [--queue QUEUE]", $output->fetch());
    }
}

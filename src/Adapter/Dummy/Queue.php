<?php

namespace Mizmoz\Queue\Adapter\Dummy;

use Mizmoz\Queue\Contract\DummyAdapterInterface;

class Queue extends \Mizmoz\Queue\Adapter\Memory\Queue implements DummyAdapterInterface
{
}
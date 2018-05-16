<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

class InMemoryTaskReceiver extends InMemoryReceiver
{
    protected $type = 'task';
}

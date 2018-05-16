<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

class InMemoryQueuedPathReducerReceiver extends InMemoryReceiver
{
    protected $type = 'queued-path-reducer';
}

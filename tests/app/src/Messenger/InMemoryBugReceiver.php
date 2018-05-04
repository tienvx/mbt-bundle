<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\ReceiverInterface;

class InMemoryBugReceiver implements ReceiverInterface
{
    use InMemoryReceiverTrait;

    private $type = 'bug';
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\ReceiverInterface;

class InMemoryTaskReceiver implements ReceiverInterface
{
    use InMemoryReceiverTrait;

    private $type = 'task';
}

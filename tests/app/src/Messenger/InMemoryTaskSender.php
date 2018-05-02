<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\SenderInterface;

class InMemoryTaskSender implements SenderInterface
{
    use InMemorySenderTrait;

    private $type = 'task';
}

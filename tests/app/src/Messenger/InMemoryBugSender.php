<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\SenderInterface;

class InMemoryBugSender implements SenderInterface
{
    use InMemorySenderTrait;

    private $type = 'bug';
}

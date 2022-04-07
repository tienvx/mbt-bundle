<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler
 *
 * @uses \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
 */
class RecordVideoMessageHandlerTest extends TestCase
{
    protected BugHelperInterface $bugHelper;
    protected RecordVideoMessageHandler $handler;

    protected function setUp(): void
    {
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->handler = new RecordVideoMessageHandler($this->bugHelper);
    }

    public function testInvoke(): void
    {
        $this->bugHelper->expects($this->once())->method('recordVideo')->with(123);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }
}

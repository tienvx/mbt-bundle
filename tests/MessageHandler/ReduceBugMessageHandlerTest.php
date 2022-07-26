<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 */
class ReduceBugMessageHandlerTest extends TestCase
{
    protected BugHelperInterface|MockObject $bugHelper;
    protected ReduceBugMessageHandler $handler;
    protected ReduceBugMessage $message;

    protected function setUp(): void
    {
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->handler = new ReduceBugMessageHandler($this->bugHelper);
        $this->message = new ReduceBugMessage(123);
    }

    public function testInvoke(): void
    {
        $this->bugHelper->expects($this->once())->method('reduceBug')->with(123);
        call_user_func($this->handler, $this->message);
    }
}

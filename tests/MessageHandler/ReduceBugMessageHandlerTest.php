<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler
 *
 * @uses \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 */
class ReduceBugMessageHandlerTest extends TestCase
{
    protected BugHelperInterface $bugHelper;
    protected ReduceBugMessageHandler $handler;

    protected function setUp(): void
    {
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->handler = new ReduceBugMessageHandler($this->bugHelper);
    }

    public function testInvoke(): void
    {
        $this->bugHelper->expects($this->once())->method('reduceBug')->with(123);
        $message = new ReduceBugMessage(123);
        call_user_func($this->handler, $message);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage
 */
class ReduceStepsMessageHandlerTest extends TestCase
{
    protected BugHelperInterface $bugHelper;
    protected ReduceStepsMessageHandler $handler;
    protected ReduceStepsMessage $message;

    protected function setUp(): void
    {
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->handler = new ReduceStepsMessageHandler($this->bugHelper);
        $this->message = new ReduceStepsMessage(123, 6, 1, 2);
    }

    public function testInvoke(): void
    {
        $this->bugHelper->expects($this->once())->method('reduceSteps')->with(123, 6, 1, 2);
        call_user_func($this->handler, $this->message);
    }
}

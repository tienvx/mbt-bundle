<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Message\RunTaskMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\RunTaskMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\RunTaskMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\RunTaskMessage
 */
class RunTaskMessageHandlerTest extends TestCase
{
    protected TaskHelperInterface|MockObject $taskHelper;
    protected RunTaskMessageHandler $handler;
    protected RunTaskMessage $message;

    protected function setUp(): void
    {
        $this->taskHelper = $this->createMock(TaskHelperInterface::class);
        $this->handler = new RunTaskMessageHandler($this->taskHelper);
        $this->message = new RunTaskMessage(123);
    }

    public function testInvoke(): void
    {
        $this->taskHelper->expects($this->once())->method('run')->with(123);
        call_user_func($this->handler, $this->message);
    }
}

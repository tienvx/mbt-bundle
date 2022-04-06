<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Tienvx\Bundle\MbtBundle\Message\RunTaskMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\RunTaskMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;
use Tienvx\Bundle\MbtBundle\Tests\StepsTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\RunTaskMessageHandler
 *
 * @uses \Tienvx\Bundle\MbtBundle\Message\RunTaskMessage
 */
class RunTaskMessageHandlerTest extends StepsTestCase
{
    protected TaskHelperInterface $taskHelper;
    protected RunTaskMessageHandler $handler;

    protected function setUp(): void
    {
        $this->taskHelper = $this->createMock(TaskHelperInterface::class);
        $this->handler = new RunTaskMessageHandler(
            $this->taskHelper
        );
    }

    public function testInvoke(): void
    {
        $this->taskHelper->expects($this->once())->method('run')->with(123);
        $message = new RunTaskMessage(123);
        call_user_func($this->handler, $message);
    }
}

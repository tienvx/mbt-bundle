<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 */
class ReportBugMessageHandlerTest extends TestCase
{
    protected BugHelperInterface $bugHelper;
    protected ReportBugMessageHandler $handler;

    protected function setUp(): void
    {
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->handler = new ReportBugMessageHandler($this->bugHelper);
    }

    public function testInvoke(): void
    {
        $this->bugHelper->expects($this->once())->method('reportBug')->with(123);
        $message = new ReportBugMessage(123);
        call_user_func($this->handler, $message);
    }
}

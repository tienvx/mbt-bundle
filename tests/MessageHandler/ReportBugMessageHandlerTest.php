<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugNotifierInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 */
class ReportBugMessageHandlerTest extends TestCase
{
    protected EntityManagerInterface $entityManager;
    protected BugNotifierInterface $notifyHelper;
    protected ReportBugMessageHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notifyHelper = $this->createMock(BugNotifierInterface::class);
        $this->handler = new ReportBugMessageHandler(
            $this->entityManager,
            $this->notifyHelper
        );
    }

    public function testInvokeNoBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not report bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $message = new ReportBugMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeSendNotification(): void
    {
        $task = new Task();
        $task->setAuthor(22);
        $task->getTaskConfig()->setNotifyAuthor(true);
        $task->getTaskConfig()->setNotifyChannels(['email', 'chat/slack', 'sms/nexmo']);
        $bug = new Bug();
        $bug->setTitle('New bug found');
        $bug->setId(123);
        $bug->setMessage('Something wrong');
        $bug->setTask($task);
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->notifyHelper
            ->expects($this->once())
            ->method('notify')
            ->with($bug);
        $message = new ReportBugMessage(123);
        call_user_func($this->handler, $message);
    }
}

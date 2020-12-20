<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Notification\BugNotification;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\UserNotifierInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 * @covers \Tienvx\Bundle\MbtBundle\Notification\BugNotification
 */
class ReportBugMessageHandlerTest extends TestCase
{
    protected EntityManagerInterface $entityManager;
    protected NotifierInterface $notifier;
    protected BugHelperInterface $bugHelper;
    protected TranslatorInterface $translator;
    protected UserNotifierInterface $userNotifier;
    protected ReportBugMessageHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notifier = $this->createMock(NotifierInterface::class);
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->userNotifier = $this->createMock(UserNotifierInterface::class);
        $this->handler = new ReportBugMessageHandler(
            $this->entityManager,
            $this->notifier,
            $this->bugHelper,
            $this->translator,
            $this->userNotifier
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
        $this->bugHelper
            ->expects($this->once())
            ->method('buildBugUrl')
            ->with($bug)
            ->willReturn('http://localhost/bug/123');
        $this->translator->expects($this->exactly(3))->method('trans')->withConsecutive(
            ['mbt.notify.bug_id', ['id' => $bug->getId()]],
            ['mbt.notify.bug_message', ['message' => $bug->getMessage()]],
            ['mbt.notify.more_info']
        )->willReturnOnConsecutiveCalls(
            'Bug id',
            'Bug message',
            'More info'
        );
        $recipient = new Recipient('test@example.com');
        $this->userNotifier->expects($this->once())->method('getRecipient')->with(22)->willReturn($recipient);
        $this->notifier
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(BugNotification::class), $recipient);
        $message = new ReportBugMessage(123);
        call_user_func($this->handler, $message);
    }
}

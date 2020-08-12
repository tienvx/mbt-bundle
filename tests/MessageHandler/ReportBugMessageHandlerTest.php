<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Notification\BugNotification;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\BugSubscriberInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Notification\BugNotification
 */
class ReportBugMessageHandlerTest extends TestCase
{
    protected EntityManagerInterface $entityManager;
    protected NotifierInterface $notifier;
    protected ConfigLoaderInterface $configLoader;
    protected BugSubscriberInterface $bugSubscriber;
    protected BugHelperInterface $bugHelper;
    protected TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notifier = $this->createMock(NotifierInterface::class);
        $this->configLoader = $this->createMock(ConfigLoaderInterface::class);
        $this->bugSubscriber = $this->createMock(BugSubscriberInterface::class);
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
    }

    public function testInvokeNoBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('No bug found for id 123');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $message = new ReportBugMessage(123);
        $handler = new ReportBugMessageHandler($this->entityManager, $this->notifier, $this->configLoader, $this->bugSubscriber, $this->bugHelper, $this->translator);
        $handler($message);
    }

    public function testInvokeSendNotification(): void
    {
        $bug = new Bug();
        $bug->setTitle('New bug found');
        $bug->setId(123);
        $bug->setMessage('Something wrong');
        $recipient1 = new Recipient('test@example.com');
        $recipient2 = new Recipient('example@test.com', '1234567890');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->configLoader->expects($this->once())->method('getNotifyChannels')->willReturn(['email', 'chat/slack', 'sms/nexmo']);
        $this->bugHelper->expects($this->once())->method('buildBugUrl')->with($bug)->willReturn('http://localhost/bug/123');
        $this->bugSubscriber->expects($this->once())->method('getRecipies')->willReturn([
            $recipient1,
            $recipient2,
        ]);
        $this->translator->expects($this->exactly(3))->method('trans')->withConsecutive(
            ['mbt.notify.bug_id', ['id' => $bug->getId()]],
            ['mbt.notify.bug_message', ['message' => $bug->getMessage()]],
            ['mbt.notify.more_info']
        )->willReturnOnConsecutiveCalls(
            'Bug id',
            'Bug message',
            'More info'
        );
        $this->notifier->expects($this->once())->method('send')->with($this->isInstanceOf(BugNotification::class), $recipient1, $recipient2);
        $message = new ReportBugMessage(123);
        $handler = new ReportBugMessageHandler($this->entityManager, $this->notifier, $this->configLoader, $this->bugSubscriber, $this->bugHelper, $this->translator);
        $handler($message);
    }
}

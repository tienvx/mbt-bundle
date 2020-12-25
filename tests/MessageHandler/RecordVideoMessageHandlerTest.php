<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfig
 */
class RecordVideoMessageHandlerTest extends TestCase
{
    protected ProviderManager $providerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected StepsRunnerInterface $stepsRunner;
    protected RecordVideoMessageHandler $handler;

    protected function setUp(): void
    {
        $this->providerManager = $this->createMock(ProviderManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->stepsRunner = $this->createMock(StepsRunnerInterface::class);
        $this->handler = new RecordVideoMessageHandler(
            $this->providerManager,
            $this->entityManager,
            $this->stepsRunner,
            $this->messageBus
        );
    }

    public function testInvokeNoBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not record video for bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeRecordVideo(): void
    {
        $task = new Task();
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setProvider('current-provider');
        $task->setSeleniumConfig($seleniumConfig);
        $bug = new Bug();
        $bug->setId(123);
        $bug->setModelVersion(1);
        $bug->setTask($task);
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 6)));
        $provider = $this->createMock(ProviderInterface::class);
        $provider
            ->expects($this->once())
            ->method('getVideoUrl')
            ->with('http://localhost:4444', 123)
            ->willReturn('http://localhost:4444/video.mp4');
        $this->providerManager->expects($this->once())->method('get')->with('current-provider')->willReturn($provider);
        $this->providerManager
            ->expects($this->once())
            ->method('getSeleniumServer')
            ->willReturn('http://localhost:4444');
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) {
                return $message instanceof DownloadVideoMessage
                    && 123 === $message->getBugId()
                    && 'http://localhost:4444/video.mp4' === $message->getVideoUrl();
            }))
            ->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }
}

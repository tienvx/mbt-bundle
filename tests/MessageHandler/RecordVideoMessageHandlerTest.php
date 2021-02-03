<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
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
    protected StepRunnerInterface $stepRunner;
    protected RecordVideoMessageHandler $handler;
    protected TaskInterface $task;
    protected BugInterface $bug;
    protected RevisionInterface $revision;

    protected function setUp(): void
    {
        $this->providerManager = $this->createMock(ProviderManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
        $this->handler = new RecordVideoMessageHandler(
            $this->providerManager,
            $this->entityManager,
            $this->stepRunner
        );
        $this->revision = new Revision();
        $this->task = new Task();
        $this->task->setModelRevision($this->revision);
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setProvider('current-provider');
        $this->task->setSeleniumConfig($seleniumConfig);
        $this->bug = new Bug();
        $this->bug->setId(123);
        $this->bug->setTask($this->task);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
    }

    public function testInvokeNoBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not record video for bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Exception that we care about');
        $driver = $this->createMock(RemoteWebDriver::class);
        $driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task, 123)
            ->willReturn($driver);
        $this->stepRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $driver)
            ->willThrowException(new RuntimeException('Exception that we care about'));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeNotThrowException(): void
    {
        $driver = $this->createMock(RemoteWebDriver::class);
        $driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task, 123)
            ->willReturn($driver);
        $this->stepRunner
            ->expects($this->exactly(2))
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $driver)
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException(new Exception("Exception that we don't care about")),
            );
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeRecordVideo(): void
    {
        $driver = $this->createMock(RemoteWebDriver::class);
        $driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task, 123)
            ->willReturn($driver);
        $this->stepRunner
            ->expects($this->exactly(3))
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $driver);
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }
}

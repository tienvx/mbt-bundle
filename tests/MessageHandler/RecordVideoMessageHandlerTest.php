<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
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
        $revision = new Revision();
        $task = new Task();
        $task->setModelRevision($revision);
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setProvider('current-provider');
        $task->setSeleniumConfig($seleniumConfig);
        $bug = new Bug();
        $bug->setId(123);
        $bug->setTask($task);
        $bug->setSteps(
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        );
        $driver = $this->createMock(RemoteWebDriver::class);
        $driver->expects($this->once())->method('quit');
        $this->providerManager->expects($this->once())->method('createDriver')->with($task, 123)->willReturn($driver);
        $this->stepRunner->expects($this->exactly(3))
            ->method('run')->with($this->isInstanceOf(StepInterface::class), $revision, $driver);
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $message = new RecordVideoMessage(123);
        call_user_func($this->handler, $message);
    }
}

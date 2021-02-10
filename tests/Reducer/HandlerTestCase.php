<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Tests\StepsTestCase;

class HandlerTestCase extends StepsTestCase
{
    protected HandlerInterface $handler;
    protected ProviderManager $providerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected StepRunnerInterface $stepRunner;
    protected StepsBuilderInterface $stepsBuilder;
    protected BugHelperInterface $bugHelper;
    protected RemoteWebDriver $driver;
    protected array $newSteps;
    protected BugInterface $bug;
    protected TaskInterface $task;
    protected RevisionInterface $revision;

    protected function setUp(): void
    {
        $this->providerManager = $this->createMock(ProviderManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
        $this->stepsBuilder = $this->createMock(StepsBuilderInterface::class);
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->newSteps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $this->revision = $this->createMock(RevisionInterface::class);
        $this->task = new Task();
        $this->task->setId(123);
        $this->task->setModelRevision($this->revision);
        $this->bug = new Bug();
        $this->bug->setId(1);
        $this->bug->setMessage('Something wrong');
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->task->addBug($this->bug);
        $this->stepsBuilder
            ->expects($this->once())
            ->method('create')
            ->with($this->bug, 1, 2)
            ->willReturn((fn () => yield from $this->newSteps)());
        $this->driver = $this->createMock(RemoteWebDriver::class);
    }

    public function testHandleOldBug(): void
    {
        $this->driver->expects($this->never())->method('quit');
        $this->providerManager->expects($this->never())->method('createDriver');
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->stepRunner->expects($this->never())->method('run');
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRun(): void
    {
        $this->driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task)
            ->willReturn($this->driver);
        $this->stepRunner->expects($this->exactly(4))
            ->method('run')->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver);
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRunIntoException(): void
    {
        $this->driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task)
            ->willReturn($this->driver);
        $this->stepRunner->expects($this->exactly(4))->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver)
            ->will($this->onConsecutiveCalls(
                null,
                null,
                null,
                $this->throwException(new RuntimeException('Something else wrong')),
            ));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something else wrong');
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRunFoundSameBug(): void
    {
        $this->driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task)
            ->willReturn($this->driver);
        $this->entityManager->expects($this->once())->method('refresh')->with($this->bug);
        $this->entityManager
            ->expects($this->once())
            ->method('lock')
            ->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager
            ->expects($this->once())
            ->method('transactional')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ReduceBugMessage::class))
            ->willReturn(new Envelope(new \stdClass()));
        $this->stepRunner->expects($this->exactly(4))->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver)
            ->will($this->onConsecutiveCalls(
                null,
                null,
                null,
                $this->throwException(new Exception('Something wrong')),
            ));
        $this->handler->handle($this->bug, 1, 2);
        $this->assertSteps($this->newSteps, $this->bug->getSteps());
        $this->assertFalse($this->bug->isReducing());
        $this->assertSame(0, $this->bug->getProgress()->getProcessed());
        $this->assertSame(0, $this->bug->getProgress()->getTotal());
    }
}

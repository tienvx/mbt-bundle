<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Exception;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunner;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\StepsRunner
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class StepsRunnerTest extends TestCase
{
    protected array $steps;
    protected StepRunnerInterface $stepRunner;
    protected StepsRunnerInterface $stepsRunner;
    protected SelenoidHelperInterface $selenoidHelper;
    protected DesiredCapabilities $capabilities;
    protected RemoteWebDriver $driver;
    protected Revision $revision;
    protected TaskInterface $task;
    protected BugInterface $bug;
    protected MockObject $exceptionCallback;
    protected MockObject $runCallback;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->revision = new Revision();
        $this->task = new Task();
        $this->task->setModelRevision($this->revision);
        $this->bug = new Bug();
        $this->bug->setTask($this->task);
    }

    protected function setUp(): void
    {
        $this->steps = [
            new Step([], new Color(), 0),
            new Step([], new Color(), 1),
            new Step([], new Color(), 2),
            new Step([], new Color(), 3),
        ];
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
        $this->selenoidHelper = $this->createMock(SelenoidHelperInterface::class);
        $this->stepsRunner = $this->getMockBuilder(StepsRunner::class)
            ->onlyMethods(['waitForVideoContainer'])
            ->setConstructorArgs([
                $this->selenoidHelper,
                $this->stepRunner,
            ])
            ->getMock();
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->capabilities = new DesiredCapabilities();
        $this->exceptionCallback = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        $this->runCallback = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunCanNotCreateDriver(
        TaskInterface|BugInterface $entity,
        bool $debug,
        bool $hasExceptionCallback,
        bool $hasRunCallback
    ): void {
        $exception = new Exception('can not create driver');
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($entity, $debug)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willThrowException($exception);
        $this->driver->expects($this->never())->method('quit');
        $this->stepRunner->expects($this->never())->method('run');
        if ($hasExceptionCallback) {
            $this->exceptionCallback->expects($this->once())->method('__invoke')->with($exception, null);
        } else {
            $this->exceptionCallback->expects($this->never())->method('__invoke');
        }
        $this->runCallback->expects($this->never())->method('__invoke');
        $this->stepsRunner->run(
            $this->steps,
            $entity,
            $debug,
            $hasExceptionCallback ? $this->exceptionCallback : null,
            $hasRunCallback ? $this->runCallback : null
        );
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunInvalidSteps(
        TaskInterface|BugInterface $entity,
        bool $debug,
        bool $hasExceptionCallback,
        bool $hasRunCallback
    ): void {
        $this->expectExceptionObject(
            new UnexpectedValueException(sprintf('Step must be instance of "%s".', StepInterface::class))
        );
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($entity, $debug)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->stepRunner->expects($this->never())->method('run');
        $this->exceptionCallback->expects($this->never())->method('__invoke');
        $this->runCallback->expects($this->never())->method('__invoke');
        $this->stepsRunner->expects($this->exactly($debug))->method('waitForVideoContainer');
        $this->stepsRunner->run(
            [new \stdClass()],
            $entity,
            $debug,
            $hasExceptionCallback ? $this->exceptionCallback : null,
            $hasRunCallback ? $this->runCallback : null
        );
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunNotFoundBugAndNotReachMaxSteps(
        TaskInterface|BugInterface $entity,
        bool $debug,
        bool $hasExceptionCallback,
        bool $hasRunCallback
    ): void {
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($entity, $debug)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->stepRunner
            ->expects($this->exactly(count($this->steps)))
            ->method('run')
            ->withConsecutive(...array_map(
                fn (StepInterface $step) => [$step, $this->revision, $this->driver],
                $this->steps
            ));
        $this->exceptionCallback->expects($this->never())->method('__invoke');
        $this->runCallback
            ->expects($this->exactly($hasRunCallback ? count($this->steps) : 0))
            ->method('__invoke')
            ->withConsecutive(...array_map(
                fn (StepInterface $step) => [$step],
                $this->steps
            ))
            ->willReturn(false);
        $this->stepsRunner->expects($this->exactly($debug))->method('waitForVideoContainer');
        $this->stepsRunner->run(
            $this->steps,
            $entity,
            $debug,
            $hasExceptionCallback ? $this->exceptionCallback : null,
            $hasRunCallback ? $this->runCallback : null
        );
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunFoundBugAtThirdStep(
        TaskInterface|BugInterface $entity,
        bool $debug,
        bool $hasExceptionCallback,
        bool $hasRunCallback
    ): void {
        $exception = new Exception('Can not run the third step');
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($entity, $debug)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->stepRunner
            ->expects($this->exactly(3))
            ->method('run')
            ->withConsecutive(...array_map(
                fn (StepInterface $step) => [$step, $this->revision, $this->driver],
                array_slice($this->steps, 0, 3)
            ))
            ->will($this->onConsecutiveCalls(
                null,
                null,
                $this->throwException($exception),
            ));
        $this->exceptionCallback
            ->expects($this->exactly($hasExceptionCallback))
            ->method('__invoke')
            ->with($exception, $this->steps[2]);
        $this->runCallback
            ->expects($this->exactly($hasRunCallback ? 2 : 0))
            ->method('__invoke')
            ->withConsecutive(...array_map(
                fn (StepInterface $step) => [$step],
                array_slice($this->steps, 0, 2)
            ))
            ->willReturn(false);
        $this->stepsRunner->expects($this->exactly($debug))->method('waitForVideoContainer');
        $this->stepsRunner->run(
            $this->steps,
            $entity,
            $debug,
            $hasExceptionCallback ? $this->exceptionCallback : null,
            $hasRunCallback ? $this->runCallback : null
        );
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunReachMaxSteps2(
        TaskInterface|BugInterface $entity,
        bool $debug,
        bool $hasExceptionCallback,
        bool $hasRunCallback
    ): void {
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($entity, $debug)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->stepRunner
            ->expects($this->exactly($hasRunCallback ? 2 : count($this->steps)))
            ->method('run')
            ->withConsecutive(...array_map(
                fn (StepInterface $step) => [$step, $this->revision, $this->driver],
                $hasRunCallback ? array_slice($this->steps, 0, 2) : $this->steps
            ));
        $this->exceptionCallback->expects($this->never())->method('__invoke');
        $this->runCallback
            ->expects($this->exactly($hasRunCallback ? 2 : 0))
            ->method('__invoke')
            ->withConsecutive(...array_map(
                fn (StepInterface $step) => [$step],
                array_slice($this->steps, 0, 2)
            ))
            ->willReturnOnConsecutiveCalls(false, true);
        $this->stepsRunner->expects($this->exactly($debug))->method('waitForVideoContainer');
        $this->stepsRunner->run(
            $this->steps,
            $entity,
            $debug,
            $hasExceptionCallback ? $this->exceptionCallback : null,
            $hasRunCallback ? $this->runCallback : null
        );
    }

    public function entityProvider(): array
    {
        return [
            [$this->task, true, false, false],
            [$this->task, true, true, false],
            [$this->task, true, false, true],
            [$this->task, true, true, true],
            [$this->task, false, false, false],
            [$this->task, false, true, false],
            [$this->task, false, false, true],
            [$this->task, false, true, true],
            [$this->bug, true, false, false],
            [$this->bug, true, true, false],
            [$this->bug, true, false, true],
            [$this->bug, true, true, true],
            [$this->bug, false, false, false],
            [$this->bug, false, true, false],
            [$this->bug, false, false, true],
            [$this->bug, false, true, true],
        ];
    }
}

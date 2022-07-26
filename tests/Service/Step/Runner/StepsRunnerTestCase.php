<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Runner;

use Exception;
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
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

abstract class StepsRunnerTestCase extends TestCase
{
    protected array $steps;
    protected StepRunnerInterface|MockObject $stepRunner;
    protected StepsRunnerInterface $stepsRunner;
    protected SelenoidHelperInterface|MockObject $selenoidHelper;
    protected RemoteWebDriver|MockObject $driver;
    protected Revision $revision;
    protected TaskInterface $task;
    protected BugInterface $bug;
    protected MockObject $handleException;

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
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->handleException = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunCanNotCreateDriver(DebugInterface $entity): void
    {
        $exception = new Exception('can not create driver');
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($entity)
            ->willThrowException($exception);
        $this->driver->expects($this->never())->method('quit');
        $this->assertRunSteps();
        $this->assertHandlingException($exception);
        $this->stepsRunner->run($this->steps, $entity, $this->handleException);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunInvalidSteps(DebugInterface $entity): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(sprintf('Step must be instance of "%s".', StepInterface::class))
        );
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($entity)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->assertRunSteps();
        $this->handleException->expects($this->never())->method('__invoke');
        $this->stepsRunner->run([new \stdClass()], $entity, $this->handleException);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunNotFoundBug(DebugInterface $entity): void
    {
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($entity)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->assertRunSteps($this->steps);
        $this->handleException->expects($this->never())->method('__invoke');
        $this->stepsRunner->run($this->steps, $entity, $this->handleException);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunFoundBugAtThirdStep(DebugInterface $entity): void
    {
        $bugSteps = array_slice($this->steps, 0, 3);
        $exception = new Exception('Can not run the third step');
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($entity)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->assertRunSteps($bugSteps, $exception);
        $this->assertHandlingException($exception, $bugSteps);
        $this->stepsRunner->run($this->steps, $entity, $this->handleException);
    }

    abstract protected function assertHandlingException(Exception $exception, array $bugSteps = []): void;

    protected function assertRunSteps(array $steps = [], ?Exception $exception = null): void
    {
        if ($steps) {
            $mock = $this->stepRunner
                ->expects($this->exactly(count($steps)))
                ->method('run')
                ->withConsecutive(...array_map(
                    fn (StepInterface $step) => [$step, $this->revision, $this->driver],
                    $steps
                ));
            if ($exception) {
                $mock->will($this->onConsecutiveCalls(
                    ...[...array_fill(0, count($steps) - 1, null), $this->throwException($exception)],
                ));
            }
        } else {
            $this->stepRunner->expects($this->never())->method('run');
        }
    }

    public function entityProvider(): array
    {
        return [
            [$this->task],
            [$this->bug],
        ];
    }
}

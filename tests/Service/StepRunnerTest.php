<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Factory\Model\CommandFactory;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\CommandRunner;
use Tienvx\Bundle\MbtBundle\Service\StepRunner;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\StepRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\CommandFactory
 */
class StepRunnerTest extends TestCase
{
    protected ModelInterface $model;
    protected StepInterface $step;
    protected array $commands = [];
    protected CommandRunner $commandRunner;
    protected RemoteWebDriver $driver;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->model = new Model();
        $transitions = [
            $transition = new Transition(),
        ];
        $transition->setActions([
            $command1 = CommandFactory::create(CommandInterface::OPEN, ''),
            $command2 = CommandFactory::create(CommandInterface::CLICK, ''),
        ]);
        $this->model->setTransitions($transitions);
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
        ];
        $place1->setAssertions([
            $command3 = CommandFactory::create(CommandInterface::ASSERT_EDITABLE, ''),
            $command4 = CommandFactory::create(CommandInterface::ASSERT_ALERT, ''),
        ]);
        $place2->setAssertions([
            $command5 = CommandFactory::create(CommandInterface::ASSERT_TEXT, ''),
        ]);
        $this->model->setPlaces($places);
        $this->commands = [
            [$command1, $this->driver],
            [$command2, $this->driver],
            [$command3, $this->driver],
            [$command4, $this->driver],
            [$command5, $this->driver],
        ];
        $this->step = new Step([0 => 1, 1 => 1], new Color(), 0);

        $this->commandRunner = $this->createMock(CommandRunner::class);
    }

    public function testRun(): void
    {
        $this->commandRunner->expects($this->exactly(5))->method('run')->withConsecutive(...$this->commands);
        $stepRunner = new StepRunner($this->commandRunner);
        $stepRunner->run($this->step, $this->model, $this->driver);
    }
}

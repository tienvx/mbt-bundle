<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Factory\Model\CommandFactory;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

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
    protected array $commands = [];
    protected array $firstStepCommands = [];
    protected CommandRunnerManager $commandRunnerManager;
    protected RemoteWebDriver $driver;
    protected ColorInterface $color;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->model = new Model();
        $this->model->setStartCommands([
            $command1 = CommandFactory::create(WindowCommandRunner::OPEN, 'http://example.com'),
        ]);
        $this->color = $this->createMock(ColorInterface::class);
        $transitions = [
            $transition = new Transition(),
        ];
        $transition->setCommands([
            $command2 = CommandFactory::create(WindowCommandRunner::OPEN, ''),
            $command3 = CommandFactory::create(MouseCommandRunner::CLICK, ''),
        ]);
        $this->model->setTransitions($transitions);
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
        ];
        $place1->setCommands([
            $command4 = CommandFactory::create(AssertionRunner::ASSERT_EDITABLE, ''),
            $command5 = CommandFactory::create(AssertionRunner::ASSERT_ALERT, ''),
        ]);
        $place2->setCommands([
            $command6 = CommandFactory::create(AssertionRunner::ASSERT_TEXT, ''),
        ]);
        $this->model->setPlaces($places);
        $this->commands = [
            [$command2, $this->color, $this->driver],
            [$command3, $this->color, $this->driver],
            [$command4, $this->color, $this->driver],
            [$command5, $this->color, $this->driver],
            [$command6, $this->color, $this->driver],
        ];
        $this->firstStepCommands = [
            [$command1, $this->color, $this->driver],
            [$command4, $this->color, $this->driver],
            [$command5, $this->color, $this->driver],
        ];

        $this->commandRunnerManager = $this->createMock(CommandRunnerManager::class);
    }

    public function testRun(): void
    {
        $step = new Step([0 => 1, 1 => 1], $this->color, 0);
        $this->commandRunnerManager->expects($this->exactly(5))->method('run')->withConsecutive(...$this->commands);
        $stepRunner = new StepRunner($this->commandRunnerManager);
        $stepRunner->run($step, $this->model, $this->driver);
    }

    public function testRunFirstStep(): void
    {
        $step = new Step([0 => 1], $this->color, null);
        $this->commandRunnerManager
            ->expects($this->exactly(3))
            ->method('run')
            ->withConsecutive(...$this->firstStepCommands);
        $stepRunner = new StepRunner($this->commandRunnerManager);
        $stepRunner->run($step, $this->model, $this->driver);
    }
}

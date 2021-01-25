<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\StepRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory
 */
class StepRunnerTest extends TestCase
{
    protected RevisionInterface $revision;
    protected array $commands = [];
    protected CommandRunnerManager $commandRunnerManager;
    protected RemoteWebDriver $driver;
    protected ColorInterface $color;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->revision = new Revision();
        $this->color = $this->createMock(ColorInterface::class);
        $transitions = [
            $transition = new Transition(),
        ];
        $transition->setCommands([
            $command1 = CommandFactory::create(WindowCommandRunner::OPEN),
            $command2 = CommandFactory::create(MouseCommandRunner::CLICK),
        ]);
        $this->revision->setTransitions(...$transitions);
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
        ];
        $place1->setCommands([
            $command3 = CommandFactory::create(AssertionRunner::ASSERT_EDITABLE),
            $command4 = CommandFactory::create(AssertionRunner::ASSERT_ALERT),
        ]);
        $place2->setCommands([
            $command5 = CommandFactory::create(AssertionRunner::ASSERT_TEXT),
        ]);
        $this->revision->setPlaces(...$places);
        $this->commands = [
            [$command1, $this->color, $this->driver],
            [$command2, $this->color, $this->driver],
            [$command3, $this->color, $this->driver],
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
        $stepRunner->run($step, $this->revision, $this->driver);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandManager;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\StepRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\Runner\StepRunner
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory
 * @uses \Tienvx\Bundle\MbtBundle\Model\Values
 */
class StepRunnerTest extends TestCase
{
    protected RevisionInterface $revision;
    protected array $commands = [];
    protected CommandManager|MockObject $commandManager;
    protected RemoteWebDriver $driver;
    protected ColorInterface|MockObject $color;
    protected array $valuesInstances = [];

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->revision = new Revision();
        $this->color = $this->createMock(ColorInterface::class);
        $transitions = [
            $transition = new Transition(),
        ];
        $transition->setCommands([
            $command1 = CommandFactory::create('open'),
            $command2 = CommandFactory::create('click'),
        ]);
        $this->revision->setTransitions($transitions);
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
        ];
        $place1->setCommands([
            $command3 = CommandFactory::create('assertEditable'),
            $command4 = CommandFactory::create('assertAlert'),
        ]);
        $place2->setCommands([
            $command5 = CommandFactory::create('assertText'),
        ]);
        $this->revision->setPlaces($places);
        $assertValuesInstance = function (ValuesInterface $values) {
            if (!in_array($values, $this->valuesInstances, true)) {
                $this->valuesInstances[] = $values;
            }

            return true;
        };
        $this->commands = [
            [
                $command1->getCommand(),
                $command1->getTarget(),
                $command1->getValue(),
                $this->callback($assertValuesInstance),
                $this->driver,
            ],
            [
                $command2->getCommand(),
                $command2->getTarget(),
                $command2->getValue(),
                $this->callback($assertValuesInstance),
                $this->driver,
            ],
            [
                $command3->getCommand(),
                $command3->getTarget(),
                $command3->getValue(),
                $this->callback($assertValuesInstance),
                $this->driver,
            ],
            [
                $command4->getCommand(),
                $command4->getTarget(),
                $command4->getValue(),
                $this->callback($assertValuesInstance),
                $this->driver,
            ],
            [
                $command5->getCommand(),
                $command5->getTarget(),
                $command5->getValue(),
                $this->callback($assertValuesInstance),
                $this->driver,
            ],
        ];

        $this->commandManager = $this->createMock(CommandManager::class);
    }

    public function testRun(): void
    {
        $step = new Step([0 => 1, 1 => 1], $this->color, 0);
        $this->commandManager
            ->expects($this->exactly(5))
            ->method('run')
            ->withConsecutive(...$this->commands);
        $stepRunner = new StepRunner($this->commandManager);
        $stepRunner->run($step, $this->revision, $this->driver);
        $this->assertCount(3, $this->valuesInstances);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Entity\Selenium\Command;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Selenium\Helper;
use Tienvx\Bundle\MbtBundle\Service\SeleniumInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunner;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Factory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\StepRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition
 */
class StepRunnerTest extends TestCase
{
    protected TransitionInterface $transition;
    protected MarkingInterface $marking;
    protected PlaceInterface $place1;
    protected PlaceInterface $place2;
    protected StepInterface $step;
    protected array $commands = [];
    protected Helper $helper;
    protected SeleniumInterface $selenium;

    protected function setUp(): void
    {
        $factory = Factory::createColorfulFactory();
        $this->transition = $factory->createTransition();
        $this->transition->setActions([
            $command1 = new Command(),
            $command2 = new Command(),
        ]);
        $this->place1 = $factory->createPlace();
        $this->place1->setAssertions([
            $command3 = new Command(),
            $command4 = new Command(),
        ]);
        $this->place2 = $factory->createPlace();
        $this->place2->setAssertions([
            $command5 = new Command(),
        ]);
        $this->commands = [
            [$command1],
            [$command2],
            [$command3],
            [$command4],
            [$command5],
        ];
        $placeMarking1 = $factory->createPlaceMarking();
        $placeMarking1->setPlace($this->place1);
        $placeMarking2 = $factory->createPlaceMarking();
        $placeMarking2->setPlace($this->place2);
        $this->marking = $factory->createMarking();
        $this->marking->setPlaceMarkings([$placeMarking1, $placeMarking2]);
        $this->step = new Step($this->marking, $this->transition);

        $this->helper = $this->createMock(Helper::class);
        $this->selenium = $this->createMock(SeleniumInterface::class);
    }

    public function testCanNotRunByDefault(): void
    {
        $stepRunner = new StepRunner($this->selenium);
        $this->assertFalse($stepRunner->canRun());
    }

    public function testSetUp(): void
    {
        $this->selenium->expects($this->once())->method('createHelper')->willReturn($this->helper);
        $stepRunner = new StepRunner($this->selenium);
        $stepRunner->setUp();
        $this->assertTrue($stepRunner->canRun());
    }

    public function testTearDown(): void
    {
        $this->helper->expects($this->never())->method('quit');
        $stepRunner = new StepRunner($this->selenium);
        $stepRunner->tearDown();
        $this->assertFalse($stepRunner->canRun());
    }

    public function testTearDownAfterSetUp(): void
    {
        $this->selenium->expects($this->once())->method('createHelper')->willReturn($this->helper);
        $this->helper->expects($this->once())->method('quit');
        $stepRunner = new StepRunner($this->selenium);
        $stepRunner->setUp();
        $stepRunner->tearDown();
        $this->assertFalse($stepRunner->canRun());
    }

    public function testRunWithoutSetUp(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Need to set up before running step');
        $stepRunner = new StepRunner($this->selenium);
        $stepRunner->setUp();
        $stepRunner->tearDown();
        $stepRunner->run($this->step);
    }

    public function testRun(): void
    {
        $this->selenium->expects($this->once())->method('createHelper')->willReturn($this->helper);
        $this->helper->expects($this->once())->method('quit');
        $this->helper->expects($this->exactly(5))->method('replay')->withConsecutive(...$this->commands);
        $stepRunner = new StepRunner($this->selenium);
        $stepRunner->setUp();
        $stepRunner->run($this->step);
        $stepRunner->tearDown();
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Model\Place;
use Tienvx\Bundle\MbtBundle\Entity\Model\Transition;
use Tienvx\Bundle\MbtBundle\Entity\Model\Command;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Selenium\Helper;
use Tienvx\Bundle\MbtBundle\Service\SeleniumInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunner;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\StepRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 */
class StepRunnerTest extends TestCase
{
    protected ModelInterface $model;
    protected StepInterface $step;
    protected array $commands = [];
    protected Helper $helper;
    protected SeleniumInterface $selenium;

    protected function setUp(): void
    {
        $this->model = new Model();
        $this->model->setPlaces([
            $place1 = new Place(),
            $place2 = new Place(),
        ]);
        $this->model->setTransitions([
            $transition = new Transition(),
        ]);
        $transition->setActions([
            $command1 = new Command(),
            $command2 = new Command(),
        ]);
        $place1->setAssertions([
            $command3 = new Command(),
            $command4 = new Command(),
        ]);
        $place2->setAssertions([
            $command5 = new Command(),
        ]);
        $this->commands = [
            [$command1],
            [$command2],
            [$command3],
            [$command4],
            [$command5],
        ];
        $this->step = new Step([0 => 1, 1 => 1], new Color(), 0);

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
        $stepRunner->run($this->step, $this->model);
    }

    public function testRun(): void
    {
        $this->selenium->expects($this->once())->method('createHelper')->willReturn($this->helper);
        $this->helper->expects($this->once())->method('quit');
        $this->helper->expects($this->exactly(5))->method('replay')->withConsecutive(...$this->commands);
        $stepRunner = new StepRunner($this->selenium);
        $stepRunner->setUp();
        $stepRunner->run($this->step, $this->model);
        $stepRunner->tearDown();
    }
}

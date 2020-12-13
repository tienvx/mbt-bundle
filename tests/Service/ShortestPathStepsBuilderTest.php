<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Petrinet\Model\Petrinet;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStepsBuilder;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStrategyInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\ShortestPathStepsBuilder
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 */
class ShortestPathStepsBuilderTest extends TestCase
{
    public function testCreate()
    {
        $bug = new Bug();
        $steps = [
            $step1 = $this->createMock(StepInterface::class),
            $step2 = $this->createMock(StepInterface::class),
            $step3 = $this->createMock(StepInterface::class),
            $step4 = $this->createMock(StepInterface::class),
            $step5 = $this->createMock(StepInterface::class),
            $step6 = $this->createMock(StepInterface::class),
            $step7 = $this->createMock(StepInterface::class),
        ];
        $bug->setSteps($steps);
        $petrinet = new Petrinet();
        $model = new Model();
        $task = new Task();
        $task->setModel($model);
        $bug->setTask($task);
        $shortestSteps = [
            $step8 = $this->createMock(StepInterface::class),
            $step9 = $this->createMock(StepInterface::class),
        ];
        $petrinetHelper = $this->createMock(PetrinetHelperInterface::class);
        $petrinetHelper->expects($this->once())->method('build')->with($model)->willReturn($petrinet);
        $strategy = $this->createMock(ShortestPathStrategyInterface::class);
        $strategy->expects($this->once())->method('run')->with($petrinet, $step2, $step3)->willReturn($shortestSteps);
        $stepsBuilder = new ShortestPathStepsBuilder($petrinetHelper, $strategy);
        $newSteps = $stepsBuilder->create($bug, 1, 5);
        $this->assertSame([
            $step1,
            $step2,
            $step8,
            $step9,
            $step7,
        ], [...$newSteps]);
    }
}

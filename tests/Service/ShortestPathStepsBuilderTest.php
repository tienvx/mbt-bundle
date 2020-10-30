<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Steps;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepsInterface;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStepsBuilder;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStrategyInterface;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Factory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\ShortestPathStepsBuilder
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Steps
 */
class ShortestPathStepsBuilderTest extends TestCase
{
    public function testCreate()
    {
        $factory = Factory::createColorfulFactory();
        $bug = new Bug();
        $steps = new Steps();
        $steps->setSteps([
            $step1 = $this->createMock(StepInterface::class),
            $step2 = $this->createMock(StepInterface::class),
            $step3 = $this->createMock(StepInterface::class),
            $step4 = $this->createMock(StepInterface::class),
            $step5 = $this->createMock(StepInterface::class),
            $step6 = $this->createMock(StepInterface::class),
            $step7 = $this->createMock(StepInterface::class),
        ]);
        $bug->setSteps($steps);
        $petrinet = $factory->createPetrinet();
        $model = new Model();
        $model->setPetrinet($petrinet);
        $bug->setModel($model);
        $shortestSteps = [
            $step8 = $this->createMock(StepInterface::class),
            $step9 = $this->createMock(StepInterface::class),
        ];
        $strategy = $this->createMock(ShortestPathStrategyInterface::class);
        $strategy->expects($this->once())->method('run')->with($petrinet, $step2, $step3)->willReturn($shortestSteps);
        $stepsBuilder = new ShortestPathStepsBuilder($strategy);
        $newSteps = $stepsBuilder->create($bug, 1, 5);
        $this->assertInstanceOf(StepsInterface::class, $newSteps);
        $this->assertSame(5, $newSteps->getLength());
        $this->assertSame($step1, $newSteps->getSteps()[0]);
        $this->assertSame($step2, $newSteps->getSteps()[1]);
        $this->assertSame($step8, $newSteps->getSteps()[2]);
        $this->assertSame($step9, $newSteps->getSteps()[3]);
        $this->assertSame($step7, $newSteps->getSteps()[4]);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\StepHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\StepHelper
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class StepHelperTest extends TestCase
{
    public function testCloneStepsAndResetColor(): void
    {
        $steps = [
            new Step([0], new Color(), 0),
            new Step([1], new Color(), 1),
            new Step([2], new Color(), 2),
            new Step([3], new Color(), 3),
        ];
        $stepHelper = new StepHelper();
        $newSteps = $stepHelper->cloneStepsAndResetColor($steps);
        $this->assertCount(count($steps), $newSteps);
        foreach ($newSteps as $index => $newStep) {
            $this->assertInstanceOf(StepInterface::class, $newStep);
            $this->assertSame($steps[$index]->getPlaces(), $newStep->getPlaces());
            $this->assertSame($steps[$index]->getTransition(), $newStep->getTransition());
            $this->assertNotSame($steps[$index]->getColor(), $newStep->getColor());
            if ($index > 0) {
                $this->assertNotSame($steps[$index - 1]->getColor(), $newStep->getColor());
                $this->assertSame($steps[$index - 1]->getColor()->getValues(), $newStep->getColor()->getValues());
            } else {
                $this->assertSame([], $newStep->getColor()->getValues());
            }
        }
    }
}

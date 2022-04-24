<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\StepHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\StepHelper
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class StepHelperTest extends TestCase
{
    protected ModelHelperInterface $modelHelper;
    protected StepHelper $stepHelper;
    protected RevisionInterface $revision;
    protected array $startPlaces = [1, 2, 3];

    protected function setUp(): void
    {
        $this->modelHelper = $this->createMock(ModelHelperInterface::class);
        $this->stepHelper = new StepHelper($this->modelHelper);
        $this->revision = $this->createMock(RevisionInterface::class);
        $this->modelHelper
            ->expects($this->once())
            ->method('getStartPlaceIds')
            ->with($this->revision)
            ->willReturn($this->startPlaces);
    }

    public function testCloneInvalidSteps(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(sprintf('Step must be instance of "%s".', StepInterface::class))
        );
        $this->stepHelper->cloneAndResetSteps([new \stdClass()], $this->revision);
    }

    public function testCloneStepsAndResetColor(): void
    {
        $steps = [
            new Step([0], new Color(), 0),
            new Step([1], new Color(), 1),
            new Step([2], new Color(), 2),
            new Step([3], new Color(), 3),
        ];
        $newSteps = $this->stepHelper->cloneAndResetSteps($steps, $this->revision);
        $this->assertCount(count($steps), $newSteps);
        foreach ($newSteps as $index => $newStep) {
            $this->assertInstanceOf(StepInterface::class, $newStep);
            $this->assertSame($steps[$index]->getTransition(), $newStep->getTransition());
            $this->assertNotSame($steps[$index]->getColor(), $newStep->getColor());
            if ($index > 0) {
                $this->assertNotSame($steps[$index - 1]->getColor(), $newStep->getColor());
                $this->assertSame($steps[$index - 1]->getColor()->getValues(), $newStep->getColor()->getValues());
                $this->assertSame($steps[$index - 1]->getPlaces(), $newStep->getPlaces());
            } else {
                $this->assertSame([], $newStep->getColor()->getValues());
                $this->assertSame($this->startPlaces, $newStep->getPlaces());
            }
        }
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Generator;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Generator\State
 */
class StateTest extends TestCase
{
    protected StateInterface $state;

    protected function setUp(): void
    {
        $this->state = new State([], [], 5, 10);
    }

    /**
     * @dataProvider totalPlacesAndTransitionsProvider
     */
    public function testMissingPlaceOrTransition(int $places, int $transitions): void
    {
        $this->expectExceptionObject(new OutOfRangeException('State need at least 1 place and 1 transition'));
        new State([], [], $places, $transitions);
    }

    public function testPlaceCoverage(): void
    {
        $this->assertSame(0.0, $this->state->getPlaceCoverage());
        $this->state->addVisitedPlace(1);
        $this->assertSame(20.0, $this->state->getPlaceCoverage());
    }

    public function testTransitionCoverage(): void
    {
        $this->assertSame(0.0, $this->state->getTransitionCoverage());
        $this->state->addVisitedTransition(1);
        $this->assertSame(10.0, $this->state->getTransitionCoverage());
    }

    public function totalPlacesAndTransitionsProvider(): array
    {
        return [
            [0, 11],
            [12, 0],
            [0, 0],
        ];
    }
}

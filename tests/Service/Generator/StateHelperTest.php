<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Generator;

use Petrinet\Model\Marking;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\PlaceMarking;
use Petrinet\Model\Token;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\GuardedTransition;
use SingleColorPetrinet\Model\Place;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;
use Tienvx\Bundle\MbtBundle\Service\Generator\StateHelper;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Generator\StateHelper
 * @covers \Tienvx\Bundle\MbtBundle\Model\Generator\State
 */
class StateHelperTest extends TestCase
{
    /**
     * @dataProvider canStopProvider
     */
    public function testCanStop(float $transitionCoverage, float $placeCoverage, int $stepsCount, bool $canStop): void
    {
        $configLoader = $this->createMock(ConfigLoaderInterface::class);
        $stateHelper = (new StateHelper($configLoader));
        $state = new State(123, [1, 2, 3], 23, 34, 55.6, 66.7);
        $state->setTransitionCoverage($transitionCoverage);
        $state->setPlaceCoverage($placeCoverage);
        $state->setStepsCount($stepsCount);
        $this->assertSame($canStop, $stateHelper->canStop($state));
    }

    public function testUpdateState(): void
    {
        $configLoader = $this->createMock(ConfigLoaderInterface::class);
        $stateHelper = (new StateHelper($configLoader));
        $state = new State(123, [1, 2, 3], 23, 34, 55.6, 66.7);
        $state->setStepsCount(22);
        $state->setVisitedTransitions([5, 6, 7]);
        $state->setTotalTransitions(10);
        $pm1 = new PlaceMarking();
        $pm2 = new PlaceMarking();
        $pm1->setPlace($p1 = new Place());
        $p1->setId(4);
        $pm1->setTokens([new Token()]);
        $pm2->setPlace($p2 = new Place());
        $p2->setId(5);
        $pm2->setTokens([new Token(), new Token()]);
        $marking = new Marking();
        $marking->setPlaceMarkings([
            $pm1,
            $pm2,
        ]);
        $state->setTotalPlaces(10);
        $transition = new GuardedTransition();
        $transition->setId(4);
        $stateHelper->update($state, $marking, $transition);
        $this->assertSame(23, $state->getStepsCount());
        $this->assertSame([5, 6, 7, 4], $state->getVisitedTransitions());
        $this->assertSame([1, 2, 3, 4, 5], $state->getVisitedPlaces());
        $this->assertSame(40.0, $state->getTransitionCoverage());
        $this->assertSame(50.0, $state->getPlaceCoverage());
    }

    public function testGetInitState(): void
    {
        $configLoader = $this->createMock(ConfigLoaderInterface::class);
        $configLoader->expects($this->once())->method('getMaxSteps')->willReturn(123);
        $configLoader->expects($this->once())->method('getMaxTransitionCoverage')->willReturn(88.0);
        $configLoader->expects($this->once())->method('getMaxPlaceCoverage')->willReturn(99.1);
        $petrinet = $this->createMock(PetrinetInterface::class);
        $petrinet->expects($this->once())->method('getPlaces')->willReturn(range(0, 5));
        $petrinet->expects($this->once())->method('getTransitions')->willReturn(range(0, 7));
        $places = [
            0 => 1,
            2 => 3,
            4 => 5,
        ];
        $state = (new StateHelper($configLoader))->initState($petrinet, $places);
        $this->assertSame(123, $state->getMaxSteps());
        $this->assertSame([0, 2, 4], $state->getVisitedPlaces());
        $this->assertSame(6, $state->getTotalPlaces());
        $this->assertSame(8, $state->getTotalTransitions());
        $this->assertSame(88.0, $state->getMaxTransitionCoverage());
        $this->assertSame(99.1, $state->getMaxPlaceCoverage());
    }

    public function canStopProvider(): array
    {
        return [
            [55.5, 66.6, 122, false],
            [55.7, 66.6, 122, false],
            [55.5, 66.8, 122, false],
            [55.7, 66.8, 122, true],
            [55.5, 66.6, 124, true],
        ];
    }
}

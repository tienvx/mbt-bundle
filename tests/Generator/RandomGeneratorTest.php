<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\PlaceMarking;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Token;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceMarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Factory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\RandomGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Petrinet
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Generator\State
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class RandomGeneratorTest extends TestCase
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected ColorfulFactoryInterface $colorfulFactory;
    protected ConfigLoaderInterface $configLoader;
    protected PetrinetInterface $petrinet;
    protected PlaceInterface $p1;
    protected PlaceInterface $p2;
    protected PlaceInterface $p3;
    protected PlaceInterface $p4;
    protected TransitionInterface $t1;
    protected TransitionInterface $t2;
    protected MarkingInterface $marking;

    protected function setUp(): void
    {
        $this->transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $this->colorfulFactory = $this->createMock(ColorfulFactoryInterface::class);
        $this->configLoader = $this->createMock(ConfigLoaderInterface::class);
        $this->marking = $this->createMock(MarkingInterface::class);
        $builder = new SingleColorPetrinetBuilder(Factory::createColorfulFactory());
        $this->petrinet = $builder
            ->connect($this->p1 = $builder->place(), $this->t1 = $builder->transition())
            ->connect($this->t1, $this->p2 = $builder->place())
            ->connect($this->t1, $this->p3 = $builder->place())
            ->connect($this->p2, $this->t2 = $builder->transition())
            ->connect($this->p3, $this->t2)
            ->connect($this->t2, $this->p4 = $builder->place())
            ->getPetrinet();
        $this->p1->setId(1);
        $this->p1->setInit(true);
        $this->p2->setId(2);
        $this->p3->setId(3);
        $this->p4->setId(4);
        $this->t1->setId(1);
        $this->t2->setId(2);
    }

    public function testGetManager(): void
    {
        $this->assertSame(GeneratorManager::class, RandomGenerator::getManager());
    }

    public function testGetName(): void
    {
        $this->assertSame('random', RandomGenerator::getName());
    }

    public function testGenerateNoNextTransition(): void
    {
        $this->assertMarkingInitPlaces();
        $this->marking->expects($this->never())->method('getPlaceMarkings');
        $this->transitionService->expects($this->once())->method('getEnabledTransitions')->with($this->petrinet, $this->marking)->willReturn([]);
        $generator = new RandomGenerator($this->transitionService, $this->colorfulFactory, $this->configLoader);
        $count = 1;
        foreach ($generator->generate($this->petrinet) as $step) {
            ++$count;
        }
        $this->assertSame(1, $count);
    }

    public function testGenerate(): void
    {
        $this->assertMarkingInitPlaces();
        $placeMarking1 = new PlaceMarking();
        $placeMarking1->setPlace($this->p2);
        $placeMarking1->setTokens([new Token()]);
        $placeMarking2 = new PlaceMarking();
        $placeMarking2->setPlace($this->p3);
        $placeMarking1->setTokens([new Token()]);
        $placeMarking3 = new PlaceMarking();
        $placeMarking3->setPlace($this->p4);
        $placeMarking1->setTokens([new Token()]);
        $this->marking->expects($this->exactly(2))->method('getPlaceMarkings')->willReturnOnConsecutiveCalls(
            [$placeMarking1, $placeMarking2],
            [$placeMarking3],
        );
        $this->transitionService->expects($this->exactly(3))->method('getEnabledTransitions')->with($this->petrinet, $this->marking)->willReturnOnConsecutiveCalls(
            [$this->t1],
            [$this->t2],
            [],
        );
        $generator = new RandomGenerator($this->transitionService, $this->colorfulFactory, $this->configLoader);
        $count = 1;
        foreach ($generator->generate($this->petrinet) as $step) {
            ++$count;
            $this->assertInstanceOf(StepInterface::class, $step);
            $this->assertSame(2 === $count ? $this->t1 : $this->t2, $step->getTransition());
        }
        $this->assertSame(3, $count);
    }

    protected function assertMarkingInitPlaces(): void
    {
        $token = new Token();
        $placeMarking = $this->createMock(PlaceMarkingInterface::class);
        $placeMarking->expects($this->once())->method('setPlace')->with($this->p1);
        $placeMarking->expects($this->once())->method('setTokens')->with([$token]);
        $this->marking->expects($this->once())->method('setPlaceMarkings')->with([$placeMarking]);
        $this->colorfulFactory->expects($this->once())->method('createToken')->willReturn($token);
        $this->colorfulFactory->expects($this->once())->method('createPlaceMarking')->willReturn($placeMarking);
        $this->colorfulFactory->expects($this->once())->method('createMarking')->willReturn($this->marking);
        $this->configLoader->expects($this->once())->method('getMaxSteps')->willReturn(10);
        $this->configLoader->expects($this->once())->method('getMaxTransitionCoverage')->willReturn(100.0);
        $this->configLoader->expects($this->once())->method('getMaxPlaceCoverage')->willReturn(100.0);
    }
}

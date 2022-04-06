<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Service\GuardedTransitionService;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\RandomGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Generator\State
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 * @uses \Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper
 * @uses \Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper
 * @uses \Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper
 */
class RandomGeneratorTest extends TestCase
{
    protected TaskInterface $task;
    protected RandomGenerator $generator;

    protected function setUp(): void
    {
        $factory = new ColorfulFactory();
        $expressionLanguage = new ExpressionLanguage();
        $petrinetHelper = new PetrinetHelper($factory, $expressionLanguage);
        $markingHelper = new MarkingHelper($factory);
        $modelHelper = new ModelHelper();
        $transitionService = new GuardedTransitionService($factory);
        $revision = new Revision();
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
            $place3 = new Place(),
        ];
        $revision->setPlaces($places);
        $transitions = [
            $transition1 = new Transition(),
            $transition2 = new Transition(),
            $transition3 = new Transition(),
        ];
        $transition1->setFromPlaces([]);
        $transition1->setToPlaces([0]);
        $transition2->setFromPlaces([0]);
        $transition2->setToPlaces([1]);
        $transition3->setFromPlaces([1]);
        $transition3->setToPlaces([2]);
        $revision->setTransitions($transitions);
        $this->task = new Task();
        $this->task->setModelRevision($revision);
        $this->generator = new RandomGenerator(
            $petrinetHelper,
            $markingHelper,
            $modelHelper,
            $transitionService
        );
    }

    public function testGetManager(): void
    {
        $this->assertSame(GeneratorManager::class, RandomGenerator::getManager());
    }

    public function testGetName(): void
    {
        $this->assertSame('random', RandomGenerator::getName());
    }

    public function testIsSupported(): void
    {
        $this->assertTrue(RandomGenerator::isSupported());
    }

    public function testGenerate(): void
    {
        $count = 0;
        $steps = [
            ['transition' => 0, 'places' => [0 => 1], 'color' => []],
            ['transition' => 1, 'places' => [1 => 1], 'color' => []],
            ['transition' => 2, 'places' => [2 => 1], 'color' => []],
        ];
        foreach ($this->generator->generate($this->task) as $index => $step) {
            $this->assertInstanceOf(StepInterface::class, $step);
            $this->assertSame($steps[$index]['transition'], $step->getTransition());
            $this->assertSame($steps[$index]['places'], $step->getPlaces());
            $this->assertSame($steps[$index]['color'], $step->getColor()->getValues());
            ++$count;
        }
        $this->assertSame(3, $count);
    }
}

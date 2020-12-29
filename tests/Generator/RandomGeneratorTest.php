<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Service\ExpressionLanguageEvaluator;
use SingleColorPetrinet\Service\GuardedTransitionService;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\ToPlace;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\RandomGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Model\Generator\State
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\ToPlace
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper
 * @covers \Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper
 */
class RandomGeneratorTest extends TestCase
{
    protected TaskInterface $task;
    protected RandomGenerator $generator;

    protected function setUp(): void
    {
        $factory = new ColorfulFactory();
        $petrinetHelper = new PetrinetHelper($factory);
        $markingHelper = new MarkingHelper($factory);
        $modelHelper = new ModelHelper();
        $expressionLanguage = new ExpressionLanguage();
        $expressionEvaluator = new ExpressionLanguageEvaluator($expressionLanguage);
        $transitionService = new GuardedTransitionService($factory, $expressionEvaluator);
        $model = new Model();
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
            $place3 = new Place(),
        ];
        $place1->setInit(true);
        $model->setPlaces($places);
        $transitions = [
            $transition1 = new Transition(),
            $transition2 = new Transition(),
        ];
        $transition1->setFromPlaces([0]);
        $transition1->setToPlaces([
            $toPlace1 = new ToPlace(),
        ]);
        $toPlace1->setPlace(1);
        $transition2->setFromPlaces([1]);
        $transition2->setToPlaces([
            $toPlace2 = new ToPlace(),
        ]);
        $toPlace2->setPlace(2);
        $model->setTransitions($transitions);
        $this->task = new Task();
        $this->task->setModel($model);
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

    /**
     * @dataProvider configValidationProvider
     */
    public function testValidate(array $config, bool $valid)
    {
        $this->assertSame($valid, $this->generator->validate($config));
    }

    /**
     * @dataProvider configProvider
     */
    public function testGenerate(array $config, int $stepsCount): void
    {
        $this->task->getTaskConfig()->setGeneratorConfig($config);
        $this->assertCount($stepsCount, $this->generator->generate($this->task));
    }

    public function configValidationProvider(): array
    {
        return [
            [[], false],
            [['max_place_coverage' => 55], false],
            [['max_place_coverage' => 55.7], false],
            [['max_transition_coverage' => 66], false],
            [['max_transition_coverage' => 66.8], false],
            [['max_place_coverage' => 100.1, 'max_transition_coverage' => 101.2], false],
            [['max_place_coverage' => 55.7, 'max_transition_coverage' => 66.8], true],
        ];
    }

    public function configProvider(): array
    {
        return [
            [['max_place_coverage' => 0, 'max_transition_coverage' => 0], 1],
            [['max_place_coverage' => 0.1, 'max_transition_coverage' => 0.1], 2],
            [['max_place_coverage' => 60.0, 'max_transition_coverage' => 50.0], 2],
            [['max_place_coverage' => 70.0, 'max_transition_coverage' => 60.0], 3],
        ];
    }
}

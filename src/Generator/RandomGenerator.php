<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class RandomGenerator extends AbstractGenerator
{
    public const MAX_TRANSITION_COVERAGE = 'max_transition_coverage';
    public const MAX_PLACE_COVERAGE = 'max_place_coverage';

    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected ModelHelperInterface $modelHelper;
    protected GuardedTransitionServiceInterface $transitionService;

    public function __construct(
        PetrinetHelperInterface $petrinetHelper,
        MarkingHelperInterface $markingHelper,
        ModelHelperInterface $modelHelper,
        GuardedTransitionServiceInterface $transitionService
    ) {
        $this->petrinetHelper = $petrinetHelper;
        $this->markingHelper = $markingHelper;
        $this->modelHelper = $modelHelper;
        $this->transitionService = $transitionService;
    }

    public static function getName(): string
    {
        return 'random';
    }

    public function generate(TaskInterface $task): iterable
    {
        $petrinet = $this->petrinetHelper->build($task->getModel());
        $places = $this->modelHelper->getInitPlaces($task->getModel());
        $marking = $this->markingHelper->getMarking($petrinet, $places);
        $state = new State(array_keys($places), count($petrinet->getPlaces()), count($petrinet->getTransitions()));

        while (!$this->canStop($state, $task->getTaskConfig()->getGeneratorConfig())) {
            $transition = $this->nextTransition($petrinet, $marking);
            if (is_null($transition)) {
                break;
            }

            yield new Step($this->markingHelper->getPlaces($marking), $marking->getColor(), $transition->getId());

            $this->update($state, $marking, $transition);
        }
    }

    public function validate(array $config): bool
    {
        $maxPlaceCoverage = $config[static::MAX_PLACE_COVERAGE] ?? null;
        $maxTransitionCoverage = $config[static::MAX_TRANSITION_COVERAGE] ?? null;
        return
            is_float($maxPlaceCoverage) && $maxPlaceCoverage <= 100
            && is_float($maxTransitionCoverage) && $maxTransitionCoverage <= 100;
    }

    protected function canStop(StateInterface $state, array $config): bool
    {
        $maxPlaceCoverage = $config[static::MAX_PLACE_COVERAGE] ?? 100;
        $maxTransitionCoverage = $config[static::MAX_TRANSITION_COVERAGE] ?? 100;
        return (
            $state->getTransitionCoverage() >= $maxTransitionCoverage &&
            $state->getPlaceCoverage() >= $maxPlaceCoverage
        );
    }

    protected function update(StateInterface $state, MarkingInterface $marking, TransitionInterface $transition): void
    {
        // Update visited places and transitions.
        foreach ($marking->getPlaceMarkings() as $placeMarking) {
            if (count($placeMarking->getTokens()) > 0) {
                $state->addVisitedPlace($placeMarking->getPlace()->getId());
            }
        }
        $state->addVisitedTransition($transition->getId());

        // Update current coverage.
        $state->setTransitionCoverage(count($state->getVisitedTransitions()) / $state->getTotalTransitions() * 100);
        $state->setPlaceCoverage(count($state->getVisitedPlaces()) / $state->getTotalPlaces() * 100);
    }

    protected function nextTransition(PetrinetInterface $petrinet, MarkingInterface $marking): ?TransitionInterface
    {
        $transitions = $this->transitionService->getEnabledTransitions($petrinet, $marking);
        if (count($transitions) > 0) {
            $key = array_rand($transitions);

            return $transitions[$key] instanceof TransitionInterface ? $transitions[$key] : null;
        }

        return null;
    }
}

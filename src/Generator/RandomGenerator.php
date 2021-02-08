<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

class RandomGenerator extends AbstractGenerator
{
    public const MAX_TRANSITION_COVERAGE = 'maxTransitionCoverage';
    public const MAX_PLACE_COVERAGE = 'maxPlaceCoverage';

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
        $petrinet = $this->petrinetHelper->build($task->getModelRevision());
        $transition = null;
        $transitionId = $this->modelHelper->getStartTransitionId($task->getModelRevision());
        $places = $this->modelHelper->getStartPlaceIds($task->getModelRevision());
        $marking = $this->markingHelper->getMarking($petrinet, $places);
        $state = new State(
            [$transitionId],
            count($task->getModelRevision()->getPlaces()),
            count($task->getModelRevision()->getTransitions())
        );

        while ($this->canContinue($state, $task->getTaskConfig()->getGeneratorConfig())) {
            if ($transition) {
                $this->transitionService->fire($transition, $marking);
                $places = $this->markingHelper->getPlaces($marking);
            }
            $this->update($state, $marking, $transitionId);

            yield new Step(
                $places,
                $marking->getColor(),
                $transitionId
            );

            $transition = $this->nextTransition($petrinet, $marking);
            if (!$transition) {
                break;
            }

            $transitionId = $transition->getId();
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

    protected function canContinue(StateInterface $state, array $config): bool
    {
        $maxPlaceCoverage = $config[static::MAX_PLACE_COVERAGE] ?? 100;
        $maxTransitionCoverage = $config[static::MAX_TRANSITION_COVERAGE] ?? 100;

        return
            $state->getTransitionCoverage() < $maxTransitionCoverage
            || $state->getPlaceCoverage() < $maxPlaceCoverage
        ;
    }

    protected function update(StateInterface $state, MarkingInterface $marking, int $transitionId): void
    {
        // Update visited places and transitions.
        foreach ($marking->getPlaceMarkings() as $placeMarking) {
            if (count($placeMarking->getTokens()) > 0) {
                $state->addVisitedPlace($placeMarking->getPlace()->getId());
            }
        }
        $state->addVisitedTransition($transitionId);

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

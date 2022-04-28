<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

class RandomGenerator extends AbstractGenerator
{
    public const MAX_TRANSITION_COVERAGE = 100;
    public const MAX_PLACE_COVERAGE = 100;

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
        $transition = $petrinet->getTransitionById(
            $this->modelHelper->getStartTransitionId($task->getModelRevision())
        );
        $marking = $this->markingHelper->getMarking($petrinet, [PetrinetHelper::FAKE_START_PLACE_ID => 1]);
        $state = new State(
            [],
            [],
            count($task->getModelRevision()->getPlaces()),
            count($task->getModelRevision()->getTransitions())
        );

        while ($this->canContinue($state)) {
            $this->transitionService->fire($transition, $marking);
            $this->update($state, $marking, $transition->getId());

            yield new Step(
                $this->markingHelper->getPlaces($marking),
                $marking->getColor(),
                $transition->getId()
            );

            $transition = $this->nextTransition($petrinet, $marking);
            if (!$transition) {
                break;
            }
        }
    }

    protected function canContinue(StateInterface $state): bool
    {
        return
            $state->getTransitionCoverage() < static::MAX_TRANSITION_COVERAGE
            || $state->getPlaceCoverage() < static::MAX_PLACE_COVERAGE
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

<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\Generator\StateHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class RandomGenerator extends AbstractGenerator
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected ModelHelperInterface $modelHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected StateHelperInterface $stateHelper;

    public function __construct(
        PetrinetHelperInterface $petrinetHelper,
        MarkingHelperInterface $markingHelper,
        ModelHelperInterface $modelHelper,
        GuardedTransitionServiceInterface $transitionService,
        StateHelperInterface $stateHelper
    ) {
        $this->petrinetHelper = $petrinetHelper;
        $this->markingHelper = $markingHelper;
        $this->modelHelper = $modelHelper;
        $this->transitionService = $transitionService;
        $this->stateHelper = $stateHelper;
    }

    public static function getName(): string
    {
        return 'random';
    }

    public function generate(ModelInterface $model): iterable
    {
        $petrinet = $this->petrinetHelper->build($model);
        $places = $this->modelHelper->getInitPlaces($model);
        $marking = $this->markingHelper->getMarking($petrinet, $places);
        $state = $this->stateHelper->initState($petrinet, $places);

        while (!$this->stateHelper->canStop($state)) {
            $transition = $this->nextTransition($petrinet, $marking);
            if (is_null($transition)) {
                break;
            }

            yield new Step($this->markingHelper->getPlaces($marking), $marking->getColor(), $transition->getId());

            $this->stateHelper->update($state, $marking, $transition);
        }
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

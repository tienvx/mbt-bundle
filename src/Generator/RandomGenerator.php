<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class RandomGenerator extends AbstractGenerator
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected ModelHelperInterface $modelHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected ConfigLoaderInterface $configLoader;

    public function __construct(
        PetrinetHelperInterface $petrinetHelper,
        MarkingHelperInterface $markingHelper,
        ModelHelperInterface $modelHelper,
        GuardedTransitionServiceInterface $transitionService,
        ConfigLoaderInterface $configLoader
    ) {
        $this->petrinetHelper = $petrinetHelper;
        $this->markingHelper = $markingHelper;
        $this->modelHelper = $modelHelper;
        $this->transitionService = $transitionService;
        $this->configLoader = $configLoader;
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

        $state = new State(
            $this->configLoader->getMaxSteps(),
            array_keys($places),
            count($petrinet->getPlaces()),
            count($petrinet->getTransitions()),
            $this->configLoader->getMaxTransitionCoverage(),
            $this->configLoader->getMaxPlaceCoverage(),
        );

        while (!$state->canStop()) {
            $transition = $this->nextTransition($petrinet, $marking);
            if (is_null($transition)) {
                break;
            }

            yield new Step($this->markingHelper->getPlaces($marking), $marking->getColor()->getColor(), $transition->getId());

            $state->update($marking, $transition);
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

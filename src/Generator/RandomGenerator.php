<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Petrinet\Builder\MarkingBuilder;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;

class RandomGenerator extends AbstractGenerator
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected ColorfulFactoryInterface $colorfulFactory;
    protected ConfigLoaderInterface $configLoader;

    public function __construct(
        GuardedTransitionServiceInterface $transitionService,
        ColorfulFactoryInterface $colorfulFactory,
        ConfigLoaderInterface $configLoader
    ) {
        $this->transitionService = $transitionService;
        $this->colorfulFactory = $colorfulFactory;
        $this->configLoader = $configLoader;
    }

    public static function getName(): string
    {
        return 'random';
    }

    public function generate(PetrinetInterface $petrinet): iterable
    {
        $markingBuilder = new MarkingBuilder($this->colorfulFactory);
        $this->markInitPlaces($petrinet, $markingBuilder);
        $marking = $markingBuilder->getMarking();
        $state = new State(
            $this->configLoader->getMaxSteps(),
            $petrinet->getInitPlaceIds(),
            count($petrinet->getPlaces()),
            count($petrinet->getTransitions()),
            $this->configLoader->getMaxTransitionCoverage(),
            $this->configLoader->getMaxPlaceCoverage(),
        );

        if (!$marking instanceof MarkingInterface) {
            return;
        }

        while (!$state->canStop()) {
            $transition = $this->nextTransition($petrinet, $marking);
            if (is_null($transition)) {
                break;
            }

            yield new Step(clone $marking, $transition);

            $state->update($marking, $transition);
        }
    }

    protected function markInitPlaces(PetrinetInterface $petrinet, MarkingBuilder $markingBuilder): void
    {
        foreach ($petrinet->getPlaces() as $place) {
            if ($place instanceof PlaceInterface && in_array($place->getId(), $petrinet->getInitPlaceIds())) {
                $markingBuilder->mark($place, 1);
            }
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

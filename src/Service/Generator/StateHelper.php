<?php

namespace Tienvx\Bundle\MbtBundle\Service\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\State;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;

class StateHelper implements StateHelperInterface
{
    protected ConfigLoaderInterface $configLoader;

    public function __construct(ConfigLoaderInterface $configLoader)
    {
        $this->configLoader = $configLoader;
    }

    public function canStop(StateInterface $state): bool
    {
        return (
                $state->getTransitionCoverage() >= $state->getMaxTransitionCoverage() &&
                $state->getPlaceCoverage() >= $state->getMaxPlaceCoverage()
            ) ||
            $state->getStepsCount() >= $state->getMaxSteps();
    }

    public function update(StateInterface $state, MarkingInterface $marking, TransitionInterface $transition): void
    {
        $state->setStepsCount($state->getStepsCount() + 1);

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

    public function initState(PetrinetInterface $petrinet, array $places): StateInterface
    {
        return new State(
            $this->configLoader->getMaxSteps(),
            array_keys($places),
            count($petrinet->getPlaces()),
            count($petrinet->getTransitions()),
            $this->configLoader->getMaxTransitionCoverage(),
            $this->configLoader->getMaxPlaceCoverage(),
        );
    }
}

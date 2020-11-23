<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\PlaceInterface as PetrinetPlaceInterface;
use Petrinet\Model\TransitionInterface as PetrinetTransitionInterface;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class PetrinetHelper implements PetrinetHelperInterface
{
    protected ColorfulFactoryInterface $colorfulFactory;

    public function __construct(ColorfulFactoryInterface $colorfulFactory)
    {
        $this->colorfulFactory = $colorfulFactory;
    }

    public function build(ModelInterface $model): PetrinetInterface
    {
        $builder = new SingleColorPetrinetBuilder($this->colorfulFactory);
        $places = $this->getPlaces($model, $builder);
        $transitions = $this->getTransitions($model, $builder);
        foreach ($model->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                $this->connectPlacesToTransition(
                    array_intersect_key($places, array_flip($transition->getFromPlaces())),
                    $transitions[$index],
                    $builder
                );
                $this->connectTransitionToPlaces($transitions[$index], $transition->getToPlaces(), $places, $builder);
            }
        }

        return $builder->getPetrinet();
    }

    protected function getPlaces(ModelInterface $model, SingleColorPetrinetBuilder $builder): array
    {
        $places = [];
        foreach ($model->getPlaces() as $index => $place) {
            if ($place instanceof PlaceInterface) {
                $places[$index] = $builder->place();
                $places[$index]->setId($index);
            }
        }

        return $places;
    }

    protected function getTransitions(ModelInterface $model, SingleColorPetrinetBuilder $builder): array
    {
        $transitions = [];
        foreach ($model->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                $transitions[$index] = $builder->transition($transition->getGuard());
                $transitions[$index]->setId($index);
            }
        }

        return $transitions;
    }

    protected function connectPlacesToTransition(array $places, PetrinetTransitionInterface $transition, SingleColorPetrinetBuilder $builder): void
    {
        foreach ($places as $place) {
            if ($place instanceof PetrinetPlaceInterface) {
                $builder->connect($place, $transition, 1);
            }
        }
    }

    protected function connectTransitionToPlaces(PetrinetTransitionInterface $transition, array $toPlaces, array $places, SingleColorPetrinetBuilder $builder): void
    {
        foreach ($toPlaces as $toPlace) {
            if ($toPlace instanceof ToPlaceInterface) {
                $builder->connect($transition, $places[$toPlace->getPlace()], 1, $toPlace->getExpression());
            }
        }
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\PlaceInterface as PetrinetPlaceInterface;
use Petrinet\Model\TransitionInterface as PetrinetTransitionInterface;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;

class PetrinetHelper implements PetrinetHelperInterface
{
    protected ColorfulFactoryInterface $colorfulFactory;
    protected ExpressionLanguage $expressionLanguage;

    public function __construct(ColorfulFactoryInterface $colorfulFactory, ExpressionLanguage $expressionLanguage)
    {
        $this->colorfulFactory = $colorfulFactory;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function build(ModelInterface $model): PetrinetInterface
    {
        $builder = new SingleColorPetrinetBuilder($this->colorfulFactory);
        $places = $this->getPlaces($model, $builder);
        $transitions = $this->getTransitions($model, $builder);
        foreach ($model->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface && $this->isValidTransition($transition)) {
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
            if ($transition instanceof TransitionInterface && $this->isValidTransition($transition)) {
                $guardCallback = $transition->getGuard()
                    ? fn (ColorInterface $color): bool => (bool) $this->expressionLanguage->evaluate(
                        $transition->getGuard(),
                        $color->getValues()
                    )
                    : null;
                $transitions[$index] = $builder->transition($guardCallback);
                $transitions[$index]->setId($index);
            }
        }

        return $transitions;
    }

    protected function connectPlacesToTransition(
        array $places,
        PetrinetTransitionInterface $transition,
        SingleColorPetrinetBuilder $builder
    ): void {
        foreach ($places as $place) {
            if ($place instanceof PetrinetPlaceInterface) {
                $builder->connect($place, $transition, 1);
            }
        }
    }

    protected function connectTransitionToPlaces(
        PetrinetTransitionInterface $transition,
        array $toPlaces,
        array $places,
        SingleColorPetrinetBuilder $builder
    ): void {
        foreach ($toPlaces as $toPlace) {
            $builder->connect($transition, $places[$toPlace], 1);
        }
    }

    protected function isValidTransition(TransitionInterface $transition): bool
    {
        return !empty($transition->getFromPlaces());
    }
}

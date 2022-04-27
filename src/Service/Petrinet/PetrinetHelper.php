<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\PlaceInterface as PetrinetPlaceInterface;
use Petrinet\Model\TransitionInterface as PetrinetTransitionInterface;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
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

    public function build(RevisionInterface $revision): PetrinetInterface
    {
        $builder = new SingleColorPetrinetBuilder($this->colorfulFactory);
        $places = $this->getPlaces($revision, $builder);
        $transitions = $this->getTransitions($revision, $builder);
        foreach ($revision->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface && !$transition->isStart()) {
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

    protected function getPlaces(RevisionInterface $revision, SingleColorPetrinetBuilder $builder): array
    {
        $places = [];
        foreach ($revision->getPlaces() as $index => $place) {
            if ($place instanceof PlaceInterface) {
                $places[$index] = $builder->place();
                $places[$index]->setId($index);
            }
        }

        return $places;
    }

    protected function getTransitions(RevisionInterface $revision, SingleColorPetrinetBuilder $builder): array
    {
        $transitions = [];
        foreach ($revision->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface && !$transition->isStart()) {
                $guardCallback = $transition->getGuard()
                    ? fn (ColorInterface $color): bool => (bool) $this->expressionLanguage->evaluate(
                        $transition->getGuard(),
                        $color->getValues()
                    )
                    : null;
                $expressionCallback = $transition->getExpression()
                    ? fn (ColorInterface $color): array => (array) $this->expressionLanguage->evaluate(
                        $transition->getExpression(),
                        $color->getValues()
                    )
                    : null;
                $transitions[$index] = $builder->transition($guardCallback, $expressionCallback);
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
}

<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\PlaceInterface as PetrinetPlaceInterface;
use Petrinet\Model\TransitionInterface as PetrinetTransitionInterface;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Model\PetrinetInterface;
use Tienvx\AssignmentsEvaluator\AssignmentsEvaluator;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;

class PetrinetHelper implements PetrinetHelperInterface
{
    public const FAKE_START_PLACE_ID = -1;

    protected ColorfulFactoryInterface $colorfulFactory;
    protected ExpressionLanguage $expressionLanguage;
    protected AssignmentsEvaluator $assignmentsEvaluator;

    public function __construct(
        ColorfulFactoryInterface $colorfulFactory,
        ExpressionLanguage $expressionLanguage,
        AssignmentsEvaluator $assignmentsEvaluator
    ) {
        $this->colorfulFactory = $colorfulFactory;
        $this->expressionLanguage = $expressionLanguage;
        $this->assignmentsEvaluator = $assignmentsEvaluator;
    }

    public function build(RevisionInterface $revision): PetrinetInterface
    {
        $builder = new SingleColorPetrinetBuilder($this->colorfulFactory);
        $places = $this->getPlaces($revision, $builder);
        $transitions = $this->getTransitions($revision, $builder);
        foreach ($revision->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                if ($transition->isStart()) {
                    $builder->connect($places[self::FAKE_START_PLACE_ID], $transitions[$index], 1);
                } else {
                    $this->connectPlacesToTransition(
                        array_intersect_key($places, array_flip($transition->getFromPlaces())),
                        $transitions[$index],
                        $builder
                    );
                }
                $this->connectTransitionToPlaces($transitions[$index], $transition->getToPlaces(), $places, $builder);
            }
        }

        return $builder->getPetrinet();
    }

    protected function getPlaces(RevisionInterface $revision, SingleColorPetrinetBuilder $builder): array
    {
        $places = [];
        $places[self::FAKE_START_PLACE_ID] = $builder->place(self::FAKE_START_PLACE_ID);
        foreach ($revision->getPlaces() as $index => $place) {
            if ($place instanceof PlaceInterface) {
                $places[$index] = $builder->place($index);
            }
        }

        return $places;
    }

    protected function getTransitions(RevisionInterface $revision, SingleColorPetrinetBuilder $builder): array
    {
        $transitions = [];
        foreach ($revision->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                $transitions[$index] = $builder->transition(
                    $transition->getGuard()
                        ? fn (ColorInterface $color): bool => (bool) $this->expressionLanguage->evaluate(
                            $transition->getGuard(),
                            $color->getValues()
                        )
                        : null,
                    $transition->getExpression()
                        ? fn (ColorInterface $color): array => $this->assignmentsEvaluator->evaluate(
                            $transition->getExpression(),
                            $color->getValues()
                        )
                        : null,
                    $index
                );
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

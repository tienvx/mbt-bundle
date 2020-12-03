<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class ModelDumper implements ModelDumperInterface
{
    public function dump(ModelInterface $model): string
    {
        return sprintf(
            "digraph \"%s\" {\n%s%s%s}",
            $model->getId(),
            $this->dumpPlaces($model),
            $this->dumpTransitions($model),
            $this->dumpArcs($model),
        );
    }

    protected function dumpPlaces(ModelInterface $model): string
    {
        $places = '';
        foreach ($model->getPlaces() as $index => $place) {
            if ($place instanceof PlaceInterface) {
                $places .= $this->dumpPlace($index, $place);
            }
        }

        return $places;
    }

    protected function dumpPlace(int $index, PlaceInterface $place): string
    {
        return sprintf(
            "\"%s\" [label=\"%s\"]\n",
            "place-$index",
            $place->getLabel(),
        );
    }

    protected function dumpTransitions(ModelInterface $model): string
    {
        $transitions = '';
        foreach ($model->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                $transitions .= $this->dumpTransition($index, $transition);
            }
        }

        return $transitions;
    }

    protected function dumpTransition(int $index, TransitionInterface $transition): string
    {
        return sprintf(
            "\"%s\" [label=\"%s\" shape=box]\n",
            "transition-$index",
            $this->getTransitionLabel($transition)
        );
    }

    protected function getTransitionLabel(TransitionInterface $transition): string
    {
        return $transition->getGuard() ?
            sprintf('%s - (%s)', $transition->getLabel(), $transition->getGuard()) :
            $transition->getLabel();
    }

    protected function dumpArcs(ModelInterface $model): string
    {
        $arcs = '';

        // Process the arcs
        foreach ($model->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                foreach ($transition->getFromPlaces() as $place) {
                    $arcs .= sprintf(
                        '"%s" -> "%s" [label="%s"]',
                        "place-$place",
                        "transition-$index",
                        ''
                    );
                    $arcs .= "\n";
                }
                foreach ($transition->getToPlaces() as $toPlace) {
                    if ($toPlace instanceof ToPlaceInterface) {
                        $arcs .= sprintf(
                            '"%s" -> "%s" [label="%s"]',
                            "transition-$index",
                            "place-{$toPlace->getPlace()}",
                            $toPlace->getExpression() ? sprintf('(%s)', $toPlace->getExpression()) : ''
                        );
                        $arcs .= "\n";
                    }
                }
            }
        }

        return $arcs;
    }
}
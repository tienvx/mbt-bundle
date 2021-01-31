<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

class ModelDumper implements ModelDumperInterface
{
    public function dump(RevisionInterface $revision): string
    {
        return sprintf(
            "digraph \"%s\" {\n%s%s%s}",
            $revision->getId(),
            $this->dumpPlaces($revision),
            $this->dumpTransitions($revision),
            $this->dumpArcs($revision),
        );
    }

    protected function dumpPlaces(RevisionInterface $revision): string
    {
        $places = '';
        foreach ($revision->getPlaces() as $index => $place) {
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

    protected function dumpTransitions(RevisionInterface $revision): string
    {
        $transitions = '';
        foreach ($revision->getTransitions() as $index => $transition) {
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
            $transition->getLabel()
        );
    }

    protected function dumpArcs(RevisionInterface $revision): string
    {
        $arcs = '';

        // Process the arcs
        foreach ($revision->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                foreach ($transition->getFromPlaces() as $place) {
                    $arcs .= sprintf(
                        '"%s" -> "%s"',
                        "place-$place",
                        "transition-$index"
                    );
                    $arcs .= "\n";
                }
                foreach ($transition->getToPlaces() as $toPlace) {
                    $arcs .= sprintf(
                        '"%s" -> "%s"',
                        "transition-$index",
                        "place-$toPlace"
                    );
                    $arcs .= "\n";
                }
            }
        }

        return $arcs;
    }
}

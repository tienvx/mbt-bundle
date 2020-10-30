<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Petrinet\Dumper\GraphvizDumper;
use Petrinet\Model\ArcInterface;
use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\InputArc;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\OutputArc;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition;

class ModelDumper extends GraphvizDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(PetrinetInterface $petrinet, MarkingInterface $marking = null)
    {
        if (!$petrinet instanceof Petrinet) {
            return parent::dump($petrinet, $marking);
        }

        return sprintf(
            "digraph \"%s\" {\n%s%s%s}",
            $petrinet->getId(),
            $this->dumpPlaces($petrinet),
            $this->dumpTransitions($petrinet),
            $this->dumpArcs($petrinet),
        );
    }

    protected function dumpPlaces(Petrinet $petrinet): string
    {
        $places = '';
        foreach ($petrinet->getPlaces() as $place) {
            if ($place instanceof Place) {
                $places .= $this->dumpPlace($place);
            }
        }

        return $places;
    }

    protected function dumpPlace(Place $place): string
    {
        return sprintf(
            "\"%s\" [label=\"%s\"]\n",
            $place->getId(),
            $place->getLabel(),
        );
    }

    protected function dumpTransitions(Petrinet $petrinet): string
    {
        $transitions = '';
        foreach ($petrinet->getTransitions() as $transition) {
            if ($transition instanceof Transition) {
                $transitions .= $this->dumpTransition($transition);
            }
        }

        return $transitions;
    }

    protected function dumpTransition(Transition $transition): string
    {
        return sprintf(
            "\"%s\" [label=\"%s\" shape=box]\n",
            $transition->getId(),
            $this->getTransitionLabel($transition)
        );
    }

    protected function getTransitionLabel(Transition $transition): string
    {
        return $transition->getGuard() ? sprintf('%s - (%s)', $transition->getLabel(), $transition->getGuard()) : $transition->getLabel();
    }

    protected function getArcs(Petrinet $petrinet): array
    {
        $arcs = [];

        foreach ($petrinet->getPlaces() as $place) {
            foreach ($place->getInputArcs() as $inputArc) {
                $arcs[$inputArc->getId()] = $inputArc;
            }

            foreach ($place->getOutputArcs() as $outputArc) {
                $arcs[$outputArc->getId()] = $outputArc;
            }
        }

        foreach ($petrinet->getTransitions() as $transition) {
            foreach ($transition->getInputArcs() as $inputArc) {
                $arcs[$inputArc->getId()] = $inputArc;
            }

            foreach ($transition->getOutputArcs() as $outputArc) {
                $arcs[$outputArc->getId()] = $outputArc;
            }
        }

        return $arcs;
    }

    protected function dumpArcs(Petrinet $petrinet): string
    {
        $arcs = '';

        // Process the arcs
        foreach ($this->getArcs($petrinet) as $arc) {
            if (!$arc instanceof ArcInterface) {
                continue;
            }
            $place = $arc->getPlace();
            $transition = $arc->getTransition();
            if (!$place instanceof Place || !$transition instanceof Transition) {
                continue;
            }
            if ($arc instanceof InputArc) {
                $arcs .= sprintf(
                    '"%s" -> "%s" [label="%s"]',
                    $place->getLabel(),
                    $transition->getLabel(),
                    ''
                );
                $arcs .= "\n";
            } elseif ($arc instanceof OutputArc) {
                $arcs .= sprintf(
                    '"%s" -> "%s" [label="%s"]',
                    $transition->getLabel(),
                    $place->getLabel(),
                    $arc->getExpression() ? sprintf('(%s)', $arc->getExpression()) : ''
                );
                $arcs .= "\n";
            }
        }

        return $arcs;
    }
}

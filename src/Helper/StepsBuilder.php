<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\Data;

class StepsBuilder
{
    /**
     * @param Graph $graph
     * @param Steps $steps
     * @param int   $from
     * @param int   $to
     *
     * @return Steps
     *
     * @throws Exception
     */
    public static function createWithShortestPath(Graph $graph, Steps $steps, int $from, int $to): Steps
    {
        $fromPlaces = $steps->getPlacesAt($from);
        $toPlaces = $steps->getPlacesAt($to);
        $middleSteps = [];
        if ($fromPlaces && $toPlaces && (array_diff($fromPlaces, $toPlaces) || array_diff($toPlaces, $fromPlaces))) {
            // Get shortest path between 2 vertices by algorithm.
            $fromVertex = $graph->getVertex(VertexHelper::getId($fromPlaces));
            $toVertex = $graph->getVertex(VertexHelper::getId($toPlaces));
            $algorithm = new Dijkstra($fromVertex);
            foreach ($algorithm->getEdgesTo($toVertex) as $edge) {
                if ($edge instanceof Directed) {
                    $middleSteps[] = new Step(
                        $edge->getAttribute('name', ''),
                        new Data(),
                        $edge->getVertexEnd()->getAttribute('places', [])
                    );
                } else {
                    throw new Exception('Only support directed graph');
                }
            }
        }

        return static::create($steps, $from, $to, $middleSteps);
    }

    /**
     * @param Steps $steps
     * @param int   $from
     * @param int   $to
     *
     * @return Steps
     *
     * @throws Exception
     */
    public static function createWithoutLoop(Steps $steps, int $from, int $to): Steps
    {
        $fromPlaces = $steps->getPlacesAt($from);
        $toPlaces = $steps->getPlacesAt($to);
        if (($fromPlaces && $toPlaces &&
            !array_diff($fromPlaces, $toPlaces) &&
            !array_diff($toPlaces, $fromPlaces))) {
            return static::create($steps, $from, $to, []);
        } else {
            throw new Exception('Can not create new path without loop');
        }
    }

    /**
     * @param Steps $steps
     * @param int   $from
     * @param int   $to
     * @param array $middleSteps
     *
     * @return Steps
     *
     * @throws Exception
     */
    public static function create(Steps $steps, int $from, int $to, array $middleSteps): Steps
    {
        $newSteps = new Steps();
        foreach ($steps as $index => $step) {
            if ($index <= $from) {
                $newSteps->addStep($step);
            }
        }
        foreach ($middleSteps as $step) {
            $newSteps->addStep($step);
        }
        foreach ($steps as $index => $step) {
            if ($index > $to) {
                $newSteps->addStep($step);
            }
        }

        return $newSteps;
    }

    /**
     * @param Steps $steps
     * @param int   $from
     * @param int   $to
     *
     * @return Steps
     *
     * @throws Exception
     */
    public static function createWithoutTransition(Steps $steps, int $from, int $to): Steps
    {
        $fromPlaces = $steps->getPlacesAt($from);
        $toPlaces = $steps->getPlacesAt($to);
        if ($fromPlaces && $toPlaces &&
            count($fromPlaces) > 1 && count($toPlaces) > 1 &&
            1 === count(array_diff($fromPlaces, $toPlaces)) &&
            1 === count(array_diff($toPlaces, $fromPlaces))) {
            $steps = static::create($steps, $from, $to, []);

            $find = array_values(array_diff($toPlaces, $fromPlaces))[0];
            $replace = array_values(array_diff($fromPlaces, $toPlaces))[0];
            for ($i = $to; $i < $steps->getLength(); ++$i) {
                $places = $steps->getPlacesAt($i);
                $key = array_search($find, $places);
                $newPlaces = array_replace($places, [$key => $replace]);
                $steps->setPlacesAt($i, $newPlaces);
            }

            return $steps;
        } else {
            throw new Exception('Can not create new steps without transition');
        }
    }
}

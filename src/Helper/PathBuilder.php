<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Edges;
use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class PathBuilder
{
    /**
     * @param Graph $graph
     * @param Path  $path
     * @param int   $from
     * @param int   $to
     *
     * @return Path
     *
     * @throws Exception
     */
    public static function createWithShortestPath(Graph $graph, Path $path, int $from, int $to): Path
    {
        $fromPlaces = $path->getPlacesAt($from);
        $toPlaces = $path->getPlacesAt($to);
        if (array_diff($fromPlaces, $toPlaces)) {
            // Get shortest path between 2 vertices by algorithm.
            $fromVertex = $graph->getVertex(VertexHelper::getId($fromPlaces));
            $toVertex = $graph->getVertex(VertexHelper::getId($toPlaces));
            $algorithm = new Dijkstra($fromVertex);
            /** @var Edges $edges */
            $edges = $algorithm->getEdgesTo($toVertex);
            $middleTransitions = [];
            $middleData = array_fill(0, count($edges), null);
            $middlePlaces = [];
            foreach ($edges as $edge) {
                if ($edge instanceof Directed) {
                    $middleTransitions[] = $edge->getAttribute('name');
                    $middlePlaces[] = $edge->getVertexEnd()->getAttribute('places');
                } else {
                    throw new Exception('Only support directed graph');
                }
            }

            return static::create($path, $from, $to, $middleTransitions, $middleData, $middlePlaces);
        } else {
            return static::create($path, $from, $to, [], [], []);
        }
    }

    /**
     * @param Path $path
     * @param int  $from
     * @param int  $to
     *
     * @return Path
     *
     * @throws Exception
     */
    public static function createWithoutLoop(Path $path, int $from, int $to): Path
    {
        $fromPlaces = $path->getPlacesAt($from);
        $toPlaces = $path->getPlacesAt($to);
        if (!array_diff($fromPlaces, $toPlaces)) {
            return static::create($path, $from, $to, [], [], []);
        } else {
            throw new Exception('Can not create new path without loop');
        }
    }

    /**
     * @param Path  $path
     * @param int   $from
     * @param int   $to
     * @param array $middleTransitions
     * @param array $middleData
     * @param array $middlePlaces
     *
     * @return Path
     *
     * @throws Exception
     */
    public static function create(Path $path, int $from, int $to, array $middleTransitions, array $middleData, array $middlePlaces): Path
    {
        $beginTransitions = [];
        $endTransitions = [];
        $beginData = [];
        $endData = [];
        $beginPlaces = [];
        $endPlaces = [];
        foreach ($path as $index => $step) {
            if ($index <= $from) {
                $beginTransitions[] = $step[0];
                $beginData[] = $step[1];
                $beginPlaces[] = $step[2];
            } elseif ($index > $to) {
                $endTransitions[] = $step[0];
                $endData[] = $step[1];
                $endPlaces[] = $step[2];
            }
        }

        $transitions = array_merge($beginTransitions, $middleTransitions, $endTransitions);
        $data = array_merge($beginData, $middleData, $endData);
        $places = array_merge($beginPlaces, $middlePlaces, $endPlaces);
        $newPath = new Path($transitions, $data, $places);

        return $newPath;
    }

    /**
     * @param Path $path
     * @param int  $from
     * @param int  $to
     *
     * @return Path
     *
     * @throws Exception
     */
    public static function createWithoutTransition(Path $path, int $from, int $to): Path
    {
        $fromPlaces = $path->getPlacesAt($from);
        $toPlaces = $path->getPlacesAt($to);
        if (count($fromPlaces) > 1 && count($toPlaces) > 1 && 1 === count(array_diff($fromPlaces, $toPlaces)) &&
            1 === count(array_diff($toPlaces, $fromPlaces))) {
            $path = static::create($path, $from, $to, [], [], []);

            $find = array_values(array_diff($toPlaces, $fromPlaces))[0];
            $replace = array_values(array_diff($fromPlaces, $toPlaces))[0];
            for ($i = $to; $i < $path->countPlaces(); ++$i) {
                $places = $path->getPlacesAt($i);
                $key = array_search($find, $places);
                $newPlaces = array_replace($places, [$key => $replace]);
                $path->setPlacesAt($i, $newPlaces);
            }

            return $path;
        } else {
            throw new Exception('Can not create new path without transition');
        }
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;

class GreedyPathReducer extends AbstractPathReducer
{
    public function reduce(Path $path, Model $model, Throwable $throwable): Path
    {
        $distance = $path->countVertices() - 1;

        while ($distance > 0) {
            $pairsByDistance = [];
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                $j = $i + $distance;
                if ($j < $path->countVertices()) {
                    $pairsByDistance[$j - $i] = $pairsByDistance[$j - $i] ?? [];
                    if ($path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                        array_unshift($pairsByDistance[$j - $i], [$i, $j]);
                    }
                    elseif ($path->getVertexAt($i)->getId() !== $path->getVertexAt($j)->getId() && $distance > 1) {
                        array_push($pairsByDistance[$j - $i], [$i, $j]);
                    }
                }
            }
            krsort($pairsByDistance);
            foreach ($pairsByDistance as $pairs) {
                foreach ($pairs as $pair) {
                    list($i, $j) = $pair;
                    $newPath = $this->getNewPath($path, $i, $j);
                    // Make sure new path walkable.
                    if ($this->runner->canWalk($newPath, $model) && $newPath->countVertices() < $path->countVertices()) {
                        try {
                            $this->runner->run($newPath, $model);
                        } catch (Throwable $newThrowable) {
                            if ($newThrowable->getMessage() === $throwable->getMessage()) {
                                $path = $newPath;
                                $distance = $path->countVertices() - 1;
                                break;
                            }
                        }
                    }
                }
            }
            $distance--;
        }

        // Can not reduce the reproduce path (any more).
        return $path;
    }

    public static function getName()
    {
        return 'greedy';
    }
}

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
            for ($i = 0; $i < $distance; $i++) {
                for ($j = $distance; $j > $i; $j--) {
                    if ($path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                        $pairsByDistance[$j - $i][] = [$i, $j];
                    }
                }
            }
            krsort($pairsByDistance);
            $pairs = [];
            foreach ($pairsByDistance as $array) {
                $pairs = array_merge($pairs, $array);
            }
            for ($i = 0; $i < $distance; $i++) {
                for ($j = $distance; $j > $i; $j--) {
                    // Ignore 2 vertices are near in the path, it does not worth to reduce the path.
                    if ($path->getVertexAt($i)->getId() !== $path->getVertexAt($j)->getId() && $distance > 1 && ($j - $i) === $distance) {
                        $pairs[] = [$i, $j];
                    }
                }
            }
            foreach ($pairs as $pair) {
                list($i, $j) = $pair;
                $newPath = $this->getNewPath($path, $i, $j);
                // Make sure new path walkable.
                if ($this->runner->canWalk($newPath, $model)) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $throwable->getMessage() && !$path->equals($newPath)) {
                            $path = $newPath;
                            $distance = $path->countVertices() - 1;
                            break;
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

<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;

class LongestDistanceFirstPathReducer extends AbstractPathReducer
{
    public function reduce(Path $path, Model $model, Throwable $throwable): Path
    {
        $distance = $path->countVertices() - 1;

        while ($distance > 1) {
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                for ($j = $path->countVertices() - 1; $j > $i; $j--) {
                    if ($path->getVertexAt($i)->getId() !== $path->getVertexAt($j)->getId() && ($j - $i) === $distance) {
                        $newPath = $this->getNewPath($path, $i, $j);
                        // Make sure new path walkable.
                        if ($newPath instanceof Path && $this->runner->canWalk($newPath, $model)) {
                            try {
                                $this->runner->run($newPath, $model);
                            } catch (Throwable $newThrowable) {
                                if ($newThrowable->getMessage() === $throwable->getMessage()) {
                                    $path = $newPath;
                                    $distance = $path->countVertices() - 1;
                                    break 2;
                                }
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
        return 'longest-distance-first';
    }
}

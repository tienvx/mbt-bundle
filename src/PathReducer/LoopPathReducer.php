<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;

class LoopPathReducer extends AbstractPathReducer
{
    public function reduce(Path $path, Model $model, Throwable $throwable, $taskId = null)
    {
        $distance = $path->countEdges();

        while ($distance > 0) {
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                $j = $i + $distance;
                if ($j < $path->countVertices() && $path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                    $newPath = $this->getNewPath($path, $i, $j);
                    // Make sure new path shorter than old path.
                    if ($newPath->countEdges() < $path->countEdges()) {
                        try {
                            $this->runner->run($newPath, $model);
                        } catch (Throwable $newThrowable) {
                            if ($newThrowable->getMessage() === $throwable->getMessage()) {
                                $path = $newPath;
                                $distance = $path->countEdges();
                                break;
                            }
                        }
                    }
                }
            }
            $distance--;
        }

        // Can not reduce the reproduce path (any more).
        $this->finish($throwable->getMessage(), $path, $taskId);
    }

    public static function getName()
    {
        return 'loop';
    }
}

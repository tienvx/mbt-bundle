<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class LoopPathReducer extends AbstractPathReducer
{
    public function reduce(Path $path, string $model, string $subject, Throwable $throwable): Path
    {
        $distance = $path->countVertices() - 1;

        while ($distance > 0) {
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                $j = $i + $distance;
                if ($j < $path->countVertices() && $path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                    $newPath = $this->getNewPath($path, $i, $j);
                    // Make sure new path shorter than old path.
                    if ($newPath->countVertices() < $path->countVertices()) {
                        try {
                            $this->runner->run($newPath, $model, $subject);
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
        return 'loop';
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class GreedyPathReducer extends AbstractPathReducer
{
    /**
     * @param ReproducePath $reproducePath
     * @throws \Exception
     */
    public function reduce(ReproducePath $reproducePath)
    {
        $model = $this->modelRegistry->getModel($reproducePath->getTask()->getModel());
        $graph = $this->graphBuilder->build($model->getDefinition());
        $path  = Path::fromSteps($reproducePath->getSteps(), $graph);

        $distance = $path->countEdges();

        while ($distance > 0) {
            $pairs = [];
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                $j = $i + $distance;
                if ($j < $path->countVertices()) {
                    $pairs[] = [$i, $j];
                }
            }
            foreach ($pairs as $pair) {
                list($i, $j) = $pair;
                $newPath = $this->getNewPath($path, $i, $j);
                // Make sure new path shorter than old path.
                if ($newPath->countEdges() < $path->countEdges()) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $reproducePath->getBugMessage()) {
                            $path = $newPath;
                            $distance = $path->countEdges();
                            break;
                        }
                    }
                }
            }
            $distance--;
        }

        // Can not reduce the reproduce path (any more).
        $this->updateSteps($reproducePath, $path, $path->countEdges());
        $this->finish($reproducePath->getId());
    }

    public static function getName()
    {
        return 'greedy';
    }
}

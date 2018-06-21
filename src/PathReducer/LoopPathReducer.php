<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class LoopPathReducer extends AbstractPathReducer
{
    /**
     * @param Bug $bug
     * @throws \Exception
     */
    public function reduce(Bug $bug)
    {
        $model = $this->modelRegistry->getModel($bug->getTask()->getModel());
        $graph = $this->graphBuilder->build($model->getDefinition());
        $path  = Path::fromSteps($bug->getSteps(), $graph);

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
                            if ($newThrowable->getMessage() === $bug->getBugMessage()) {
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
        $this->updateSteps($bug, $path, $path->countEdges());
        $this->finish($bug->getId());
    }

    public static function getName()
    {
        return 'loop';
    }
}

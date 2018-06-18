<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class BinaryPathReducer extends AbstractPathReducer
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

        $try = 1;
        $quotient = floor($path->countEdges() / pow(2, $try));
        $remainder = $path->countEdges() % pow(2, $try);
        $maxTries = $path->countEdges();

        while (($try <= $maxTries && $quotient > 0) && $path->countEdges() >= 2) {
            for ($i = 0; $i < pow(2, $try); $i++) {
                $j = $quotient * $i;
                if ($i === (pow(2, $try) - 1)) {
                    $k = $quotient * ($i + 1) + $remainder;
                } else {
                    $k = $quotient * ($i + 1);
                }
                $newPath = $this->getNewPath($path, $j, $k);
                // Make sure new path shorter than old path.
                if ($newPath->countEdges() < $path->countEdges()) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $reproducePath->getBugMessage()) {
                            $path = $newPath;
                            $try = 1;
                            $maxTries = $path->countEdges();
                            break;
                        }
                    }
                }
            }
            $try++;
            $quotient = floor($path->countEdges() / pow(2, $try));
            $remainder = $path->countEdges() % pow(2, $try);
        }

        // Can not reduce the reproduce path (any more).
        $this->finish($reproducePath->getId());
    }

    public static function getName()
    {
        return 'binary';
    }
}

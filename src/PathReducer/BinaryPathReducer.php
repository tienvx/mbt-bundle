<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;

class BinaryPathReducer extends AbstractPathReducer
{
    /**
     * @param Bug $bug
     * @throws \Exception
     */
    public function reduce(Bug $bug)
    {
        $model = $bug->getTask()->getModel();
        $subject = $this->subjectManager->createSubjectForModel($model);
        $workflow = $this->workflowRegistry->get($subject, $model);
        $graph = GraphBuilder::build($workflow);
        $path  = Path::fromSteps($bug->getSteps(), $graph);

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
                        PathRunner::run($newPath, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $bug->getBugMessage()) {
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
        $this->updateSteps($bug, $path, $path->countEdges());
        $this->finish($bug->getId());
    }

    public static function getName()
    {
        return 'binary';
    }
}

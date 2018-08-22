<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;

class RandomPathReducer extends AbstractPathReducer
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
        $maxTries = $path->countEdges();

        while ($try <= $maxTries) {
            list($i, $j) = Randomizer::randomPair(0, $path->countEdges());
            $newPath = $this->getNewPath($path, $i, $j);
            // Make sure new path shorter than old path.
            if ($newPath->countEdges() < $path->countEdges()) {
                try {
                    PathRunner::run($newPath, $workflow, $subject);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                        $path = $newPath;
                    }
                } finally {
                    if ($newPath->countEdges() === $path->countEdges()) {
                        $try = 1;
                        $maxTries = $path->countEdges();
                    }
                }
            }
            $try++;
        }

        // Can not reduce the reproduce path (any more).
        $this->updateSteps($bug, $path, $path->countEdges());
        $this->finish($bug->getId());
    }

    public static function getName()
    {
        return 'random';
    }
}

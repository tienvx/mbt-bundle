<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Exception;
use Symfony\Component\Workflow\StateMachine;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;

class GreedyPathReducer extends AbstractPathReducer
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

        if (!$workflow instanceof StateMachine) {
            throw new Exception(sprintf('Path reducer %s only support model type state machine', static::getName()));
        }

        $graph = GraphBuilder::build($workflow);
        $path = PathBuilder::build($bug->getPath());

        $distance = $path->countPlaces();

        while ($distance > 0) {
            $pairs = [];
            for ($i = 0; $i < $path->countPlaces(); $i++) {
                $j = $i + $distance;
                if ($j < $path->countPlaces()) {
                    $pairs[] = [$i, $j];
                }
            }
            foreach ($pairs as $pair) {
                list($i, $j) = $pair;
                $newPath = PathBuilder::createWithShortestPath($graph, $path, $i, $j);
                // Make sure new path shorter than old path.
                if ($newPath->countPlaces() < $path->countPlaces()) {
                    try {
                        $subject = $this->subjectManager->createSubjectForModel($model);
                        PathRunner::run($newPath, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                            $path = $newPath;
                            $distance = $path->countPlaces();
                            break;
                        }
                    }
                }
            }
            $distance--;
        }

        // Can not reduce the reproduce path (any more).
        $this->updatePath($bug, $path);
        $this->finish($bug->getId());
    }

    public static function getName()
    {
        return 'greedy';
    }
}

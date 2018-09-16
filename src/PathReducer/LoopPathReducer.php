<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Exception;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;

class LoopPathReducer extends AbstractPathReducer
{
    /**
     * @param Bug $bug
     * @throws Exception
     */
    public function reduce(Bug $bug)
    {
        $model = $bug->getTask()->getModel();
        $path = PathBuilder::build($bug->getPath());
        $distance = $path->countPlaces();

        while ($distance > 0) {
            for ($i = 0; $i < $path->countPlaces(); $i++) {
                $j = $i + $distance;
                if ($j < $path->countPlaces() && !array_diff($path->getPlacesAt($i), $path->getPlacesAt($j))) {
                    $newPath = PathBuilder::createWithoutLoop($path, $i, $j);
                    // Make sure new path shorter than old path.
                    if ($newPath->countPlaces() < $path->countPlaces()) {
                        try {
                            $subject = $this->subjectManager->createSubjectForModel($model);
                            $workflow = $this->workflowRegistry->get($subject, $model);
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
            }
            $distance--;
        }

        // Can not reduce the reproduce path (any more).
        $this->updatePath($bug, $path);
        $this->finish($bug->getId());
    }

    public static function getName()
    {
        return 'loop';
    }
}

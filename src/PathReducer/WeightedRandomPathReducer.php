<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Exception;
use Symfony\Component\Workflow\StateMachine;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;

class WeightedRandomPathReducer extends AbstractPathReducer
{
    /**
     * @param Bug $bug
     * @throws Exception
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

        $pathWeight = $this->rebuildPathWeight($path);
        $try = 1;
        $maxTries = $path->countTransitions();

        while ($try <= $maxTries) {
            $vertexWeight = $this->buildVertexWeight($path, $pathWeight);
            $pairWeight = $this->buildPairWeight($vertexWeight);
            $pair = Randomizer::randomByWeight($pairWeight);
            list($i, $j) = explode('|', $pair);
            $newPath = PathBuilder::createWithShortestPath($graph, $path, $i, $j);
            // Make sure new path shorter than old path.
            if ($newPath->countTransitions() < $path->countTransitions()) {
                try {
                    $subject = $this->subjectManager->createSubjectForModel($model);
                    PathRunner::run($newPath, $workflow, $subject);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                        $path = $newPath;
                    }
                } finally {
                    if ($newPath->countTransitions() === $path->countTransitions()) {
                        $try = 1;
                        $maxTries = $path->countTransitions();
                        $pathWeight = $this->rebuildPathWeight($path, $pathWeight);
                    } else {
                        $this->updatePathWeight($pathWeight, $path, $i, $j);
                    }
                }
            }
            $try++;
        }

        // Can not reduce the reproduce path (any more).
        $this->updatePath($bug, $path, $path->countTransitions());
        $this->finish($bug->getId());
    }

    /**
     * @param array $pathWeight
     * @param Path $path
     * @param int $from
     * @param int $to
     * @throws Exception
     */
    public function updatePathWeight(array &$pathWeight, Path $path, int $from, int $to)
    {
        for ($i = $from; $i <= $to; $i++) {
            $places = $path->getPlacesAt($i);
            if (count($places) !== 1) {
                throw new Exception('Only support path with once places at a time');
            }
            $pathWeight[$places[0]]++;
        }
    }

    /**
     * @param Path $path
     * @param array|null $oldPathWeight
     * @return array
     * @throws Exception
     */
    public function rebuildPathWeight(Path $path, array $oldPathWeight = null)
    {
        $pathWeight = [];
        foreach ($path as $step) {
            $places = $step[2];
            if (count($places) !== 1) {
                throw new Exception('Only support path with once places at a time');
            }
            $pathWeight[$places[0]] = $oldPathWeight[$places[0]] ?? 0;
        }
        return $pathWeight;
    }

    public function buildPairWeight(array $vertexWeight): array
    {
        $pairs = [];
        for ($i = 0; $i < count($vertexWeight) - 1; $i++) {
            for ($j = $i; $j < count($vertexWeight); $j++) {
                $pairs["$i|$j"] = array_sum(array_slice($vertexWeight, $i, $j - $i + 1));
            }
        }
        // Revert the weight, because currently, the more weight the pair have, the more chance the pair will be picked
        // up. We want the opposite way: the less weight the pair have, the more chance the pair will be picked up.
        $max = max($pairs);
        return array_map(function ($weight) use ($max) {
            return $max - $weight;
        }, $pairs);
    }

    /**
     * @param Path $path
     * @param array $pathWeight
     * @return array
     * @throws Exception
     */
    public function buildVertexWeight(Path $path, array $pathWeight): array
    {
        $vertexWeight = [];
        foreach ($path as $index => $step) {
            $places = $step[2];
            if (count($places) !== 1) {
                throw new Exception('Only support path with once places at a time');
            }
            $vertexWeight[$index] = $pathWeight[$places[0]] ?? 0;
        }
        return $vertexWeight;
    }

    public static function getName()
    {
        return 'weighted-random';
    }
}

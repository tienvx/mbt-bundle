<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class WeightedRandomPathReducer extends AbstractPathReducer
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

        $pathWeight = $this->rebuildPathWeight($path);
        $try = 1;
        $maxTries = $path->countEdges();

        while ($try <= $maxTries) {
            $vertexWeight = $this->buildVertexWeight($path, $pathWeight);
            $pairWeight = $this->buildPairWeight($vertexWeight);
            $pair = $this->randomPairByWeight($pairWeight);
            list($i, $j) = explode('|', $pair);
            $newPath = $this->getNewPath($path, $i, $j);
            // Make sure new path shorter than old path.
            if ($newPath->countEdges() < $path->countEdges()) {
                try {
                    $this->runner->run($newPath, $model);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $reproducePath->getBugMessage()) {
                        $path = $newPath;
                    }
                } finally {
                    if ($newPath->countEdges() === $path->countEdges()) {
                        $try = 1;
                        $maxTries = $path->countEdges();
                        $pathWeight = $this->rebuildPathWeight($path, $pathWeight);
                    } else {
                        $this->updatePathWeight($pathWeight, $path, $i, $j);
                    }
                }
            }
            $try++;
        }

        // Can not reduce the reproduce path (any more).
        $this->finish($reproducePath->getId());
    }

    public function updatePathWeight(array &$pathWeight, Path $path, int $from, int $to)
    {
        for ($i = $from; $i <= $to; $i++) {
            $pathWeight[$path->getVertexAt($i)->getAttribute('name')]++;
        }
    }

    public function rebuildPathWeight(Path $path, array $oldPathWeight = null)
    {
        $pathWeight = [];
        foreach ($path->getVertices() as $vertex) {
            $pathWeight[$vertex->getAttribute('name')] = $oldPathWeight[$vertex->getAttribute('name')] ?? 0;
        }
        return $pathWeight;
    }

    /**
     * https://stackoverflow.com/a/11872928
     *
     * @param array $pairWeight
     * @return string
     */
    public function randomPairByWeight(array $pairWeight): string
    {
        $maxRand = (int) array_sum($pairWeight);
        if ($maxRand === 0) {
            $rand = rand(0, count($pairWeight) - 1);
            return array_keys($pairWeight)[$rand];
        } else {
            $rand = mt_rand(1, $maxRand);

            foreach ($pairWeight as $key => $value) {
                $rand -= $value;
                if ($rand <= 0) {
                    return $key;
                }
            }

            // Make PHP happy by return the first key.
            return array_keys($pairWeight)[0];
        }
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

    public function buildVertexWeight(Path $path, array $pathWeight): array
    {
        $vertexWeight = [];
        foreach ($path->getVertices() as $index => $vetex) {
            $vertexWeight[$index] = $pathWeight[$vetex->getAttribute('name')] ?? 0;
        }
        return $vertexWeight;
    }

    public static function getName()
    {
        return 'weighted-random';
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;

class BinaryPathReducer extends AbstractPathReducer
{
    const MAX_TRIES = 5;

    public function reduce(Path $path, Model $model, Throwable $throwable): Path
    {
        $try = 1;

        while (($try <= self::MAX_TRIES && floor($path->countVertices() / pow(2, $try)) > 1) && $path->countVertices() >= 3) {
            for ($i = 0; $i < pow(2, $try); $i++) {
                $j = floor($path->countVertices() / 2) * $i;
                $k = floor($path->countVertices() / 2) * ($i + 1);
                $newPath = $this->getNewPath($path, $j, $k);
                // Make sure new path walkable.
                if ($this->runner->canWalk($newPath, $model)) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $throwable->getMessage()) {
                            $path = $newPath;
                            $try = 1;
                            break;
                        }
                    }
                }
            }
            $try++;
        }

        // Tired of trying.
        return $path;
    }

    public static function getName()
    {
        return 'binary';
    }
}

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
        $quotient = floor($path->countEdges() / pow(2, $try));
        $remainder = $path->countEdges() % pow(2, $try);

        while (($try <= self::MAX_TRIES && $quotient > 0) && $path->countEdges() >= 2) {
            for ($i = 0; $i < pow(2, $try); $i++) {
                $j = $quotient * $i;
                if ($i === (pow(2, $try) - 1)) {
                    if ($remainder > 0) {
                        $k = $j + $remainder;
                    }
                    else {
                        break;
                    }
                }
                else {
                    $k = $quotient * ($i + 1);
                }
                $newPath = $this->getNewPath($path, $j, $k);
                // Make sure new path walkable.
                if ($this->runner->canWalk($newPath, $model)) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $throwable->getMessage() && !$path->equals($newPath)) {
                            $path = $newPath;
                            $try = 0;
                            break;
                        }
                    }
                }
            }
            $try++;
            $quotient = floor($path->countEdges() / pow(2, $try));
            $remainder = $path->countEdges() % pow(2, $try);
        }

        // Tired of trying.
        return $path;
    }

    public static function getName()
    {
        return 'binary';
    }
}

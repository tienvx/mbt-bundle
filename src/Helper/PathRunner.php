<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;

class PathRunner
{
    /**
     * @param Path $path
     * @param Model $model
     * @throws \Exception
     */
    public static function run(Path $path, Model $model)
    {
        $subject = $model->createSubject();
        $subject->setUp();

        try {
            foreach ($path->getEdges() as $index => $edge) {
                $canApply = $model->applyModel($subject, $edge, $path, $index);
                if (!$canApply) {
                    break;
                }
            }
        } finally {
            $subject->tearDown();
        }
    }
}

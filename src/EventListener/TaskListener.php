<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Process\Process;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class TaskListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof Task) {
            return;
        }

        $model = $object->getModel();
        $algorithm = $object->getAlgorithm();
        $process = new Process("bin/console mbt:test {$model} --traversal='{$algorithm}'");
        $process->run();
    }
}

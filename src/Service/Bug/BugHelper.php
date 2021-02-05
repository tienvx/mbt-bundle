<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

class BugHelper implements BugHelperInterface
{
    public function createBug(array $steps, string $message): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle('');
        $bug->setSteps($steps);
        $bug->setMessage($message);

        return $bug;
    }
}

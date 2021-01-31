<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

interface ModelDumperInterface
{
    public function dump(RevisionInterface $revision): string;
}

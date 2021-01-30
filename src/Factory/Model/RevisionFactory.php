<?php

namespace Tienvx\Bundle\MbtBundle\Factory\Model;

use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\PlaceFactory;
use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\TransitionFactory;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

class RevisionFactory
{
    public static function createFromArray(array $data): RevisionInterface
    {
        $revision = new Revision();
        $revision->setPlaces(array_map([PlaceFactory::class, 'createFromArray'], $data['places'] ?? []));
        $revision->setTransitions(array_map(
            [TransitionFactory::class, 'createFromArray'],
            $data['transitions'] ?? []
        ));

        return $revision;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Factory\Model\Revision;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;

class PlaceFactory
{
    public static function createFromArray(array $data): PlaceInterface
    {
        $place = new Place();
        $place->setLabel($data['label'] ?? '');
        $place->setCommands(
            array_map([CommandFactory::class, 'createFromArray'], $data['commands'] ?? [])
        );

        return $place;
    }
}

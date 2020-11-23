<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class ModelHelper implements ModelHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getInitPlaces(ModelInterface $model): array
    {
        $places = [];
        foreach ($model->getPlaces() as $index => $place) {
            if ($place instanceof PlaceInterface && true === $place->getInit()) {
                $places[$index] = 1;
            }
        }

        return $places;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Service\ExpressionEvaluatorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class ModelHelper implements ModelHelperInterface
{
    protected ColorfulFactoryInterface $colorfulFactory;
    protected ExpressionEvaluatorInterface $expressionEvaluator;

    public function __construct(
        ColorfulFactoryInterface $colorfulFactory,
        ExpressionEvaluatorInterface $expressionEvaluator
    ) {
        $this->colorfulFactory = $colorfulFactory;
        $this->expressionEvaluator = $expressionEvaluator;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartPlaces(ModelInterface $model): array
    {
        $places = [];
        foreach ($model->getPlaces() as $index => $place) {
            if ($place instanceof PlaceInterface && true === $place->getStart()) {
                $places[$index] = 1;
            }
        }

        return $places;
    }
}

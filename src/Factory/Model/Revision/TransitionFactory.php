<?php

namespace Tienvx\Bundle\MbtBundle\Factory\Model\Revision;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

class TransitionFactory
{
    public static function createFromArray(array $data): TransitionInterface
    {
        $transition = new Transition();
        $transition->setLabel($data['label'] ?? '');
        $transition->setGuard($data['guard'] ?? null);
        $transition->setExpression($data['expression'] ?? null);
        $transition->setCommands(
            array_map([CommandFactory::class, 'createFromArray'], ($data['commands'] ?? []))
        );
        $transition->setFromPlaces($data['fromPlaces'] ?? []);
        $transition->setToPlaces($data['toPlaces'] ?? []);

        return $transition;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Transition;

use Tienvx\Bundle\MbtBundle\Reducer\ReducerTemplate;

class TransitionReducer extends ReducerTemplate
{
    public function __construct(TransitionDispatcher $dispatcher, TransitionHandler $handler)
    {
        $this->dispatcher = $dispatcher;
        $this->handler = $handler;
    }

    public static function getName(): string
    {
        return 'transition';
    }

    public function getLabel(): string
    {
        return 'Transition';
    }

    public static function support(): bool
    {
        return true;
    }
}

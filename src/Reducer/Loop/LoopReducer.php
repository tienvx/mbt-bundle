<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Loop;

use Tienvx\Bundle\MbtBundle\Reducer\ReducerTemplate;

class LoopReducer extends ReducerTemplate
{
    public function __construct(LoopDispatcher $dispatcher, LoopHandler $handler)
    {
        $this->dispatcher = $dispatcher;
        $this->handler = $handler;
    }

    public static function getName(): string
    {
        return 'loop';
    }

    public function getLabel(): string
    {
        return 'Loop';
    }

    public static function support(): bool
    {
        return true;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Random;

use Tienvx\Bundle\MbtBundle\Reducer\ReducerTemplate;

class RandomReducer extends ReducerTemplate
{
    public function __construct(RandomDispatcher $dispatcher, RandomHandler $handler)
    {
        $this->dispatcher = $dispatcher;
        $this->handler = $handler;
    }

    public static function getName(): string
    {
        return 'random';
    }
}

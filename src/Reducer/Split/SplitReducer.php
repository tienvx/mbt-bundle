<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\ReducerTemplate;

class SplitReducer extends ReducerTemplate
{
    public function __construct(SplitDispatcher $dispatcher, SplitHandler $handler)
    {
        $this->dispatcher = $dispatcher;
        $this->handler = $handler;
    }

    public static function getName(): string
    {
        return 'split';
    }
}

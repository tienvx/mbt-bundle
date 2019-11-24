<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\HandlerTemplate;

class SplitHandler extends HandlerTemplate
{
    public static function getReducerName(): string
    {
        return SplitReducer::getName();
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Random;

use Tienvx\Bundle\MbtBundle\Reducer\HandlerTemplate;

class RandomHandler extends HandlerTemplate
{
    public static function getReducerName(): string
    {
        return RandomReducer::getName();
    }
}

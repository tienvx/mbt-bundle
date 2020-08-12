<?php

namespace Tienvx\Bundle\MbtBundle\Constant;

class Actions
{
    public const CLICK = 'click';
    public const OPEN = 'open';
    public const SET_WINDOW_SIZE = 'setWindowSize';
    public const TYPE = 'type';
    public const CLEAR = 'clear';

    public static function all()
    {
        return [
            static::CLICK,
            static::OPEN,
            static::SET_WINDOW_SIZE,
            static::TYPE,
        ];
    }
}

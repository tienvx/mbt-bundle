<?php

namespace Tienvx\Bundle\MbtBundle\Constant;

class Assertions
{
    public const ASSERT_ALERT = 'assertAlert';
    public const ASSERT_TEXT = 'assertText';
    public const ASSERT_EDITABLE = 'assertEditable';

    public static function all()
    {
        return [
            static::ASSERT_ALERT,
            static::ASSERT_TEXT,
            static::ASSERT_EDITABLE,
        ];
    }
}

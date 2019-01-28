<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

class VertexHelper
{
    public static function getId(array $places): string
    {
        if (count($places) > 1) {
            sort($places);
        }
        return json_encode($places);
    }
}

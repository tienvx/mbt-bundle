<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Data;

class DataHelper
{
    /**
     * @param Data     $data
     * @param string   $key
     * @param callable $miss
     * @param callable $validate
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function get(Data $data, string $key, callable $miss, callable $validate)
    {
        if ($data->has($key)) {
            $value = $data->get($key);
            $validate($value);
        } else {
            $value = $miss();
            $data->set($key, $value);
        }

        return $value;
    }
}

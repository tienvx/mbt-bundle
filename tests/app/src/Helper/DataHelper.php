<?php

namespace App\Helper;

use Exception;
use Tienvx\Bundle\MbtBundle\Steps\Data;

class DataHelper
{
    /**
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

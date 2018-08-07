<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

class Randomizer
{
    /**
     * https://stackoverflow.com/a/11872928
     *
     * @param array $values
     * @return mixed random key from weighted array
     */
    public static function randomByWeight(array $values)
    {
        $maxRand = (int) array_sum($values);
        if ($maxRand === 0) {
            $rand = rand(0, count($values) - 1);
            return array_keys($values)[$rand];
        } else {
            $rand = mt_rand(1, $maxRand);

            foreach ($values as $key => $value) {
                $rand -= $value;
                if ($rand <= 0) {
                    return $key;
                }
            }

            // Make PHP happy by return the first key.
            return array_keys($values)[0];
        }
    }
}

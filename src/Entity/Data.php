<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Exception;

class Data
{
    /**
     * @var array
     */
    protected $data = [];

    public function has(string $key)
    {
        return isset($this->data[$key]);
    }

    public function get(string $key)
    {
        return $this->data[$key];
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws Exception
     */
    public function set(string $key, $value)
    {
        if (!is_scalar($value)) {
            throw new Exception('Only support scalar value');
        }
        $this->data[$key] = $value;
    }

    /**
     * @return array
     */
    public function normalize(): array
    {
        $return = [];
        foreach ($this->data as $key => $value) {
            $return[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $return;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return json_encode($this->normalize());
    }

    /**
     * @param array $items
     *
     * @return Data
     *
     * @throws Exception
     */
    public static function denormalize(array $items): Data
    {
        $data = new Data();
        foreach ($items as $item) {
            if (!isset($item['key'])) {
                throw new Exception('Invalid data item: missing key');
            }
            if (!isset($item['value'])) {
                throw new Exception('Invalid data item: missing value');
            }
            $data->set($item['key'], $item['value']);
        }

        return $data;
    }

    /**
     * @param string $items
     *
     * @return Data
     *
     * @throws Exception
     */
    public static function deserialize(string $items): Data
    {
        return self::denormalize(json_decode($items, true));
    }
}

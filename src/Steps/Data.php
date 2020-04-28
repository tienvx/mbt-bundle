<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Exception;

class Data
{
    /**
     * @var array
     */
    protected $data = [];

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function get(string $key)
    {
        return $this->data[$key];
    }

    /**
     * Get value by key, then validate value, or set value if missing.
     *
     * @param string   $key      Key to get value from data
     * @param callable $miss     Call this to get value in case miss
     * @param callable $validate Call this to validate in case hit
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getSet(string $key, callable $miss, callable $validate)
    {
        if ($this->has($key)) {
            $value = $this->get($key);
            $validate($value);
        } else {
            $value = $miss();
            $this->set($key, $value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @throws Exception
     */
    public function set(string $key, $value): void
    {
        if (!is_scalar($value)) {
            throw new Exception('Only support scalar value');
        }
        $this->data[$key] = $value;
    }

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

    public function serialize(): string
    {
        return json_encode($this->normalize());
    }

    /**
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
     * @throws Exception
     */
    public static function deserialize(string $items): Data
    {
        return self::denormalize(json_decode($items, true));
    }
}

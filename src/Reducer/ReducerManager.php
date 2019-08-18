<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;

class ReducerManager
{
    /**
     * @var ReducerInterface[]
     */
    private $reducers;

    public function __construct(array $reducers = [])
    {
        $this->reducers = $reducers;
    }

    /**
     * Returns one reducer by name.
     *
     * @param $name
     *
     * @return ReducerInterface
     *
     * @throws Exception
     */
    public function getReducer($name): ReducerInterface
    {
        if (isset($this->reducers[$name])) {
            return $this->reducers[$name];
        }

        throw new Exception(sprintf('Path reducer "%s" does not exist.', $name));
    }

    /**
     * Check if there is a reducer by name.
     *
     * @param $name
     *
     * @return bool
     */
    public function hasReducer($name): bool
    {
        return isset($this->reducers[$name]);
    }

    /**
     * @return ReducerInterface[]
     */
    public function getAllReducers(): array
    {
        return $this->reducers;
    }
}

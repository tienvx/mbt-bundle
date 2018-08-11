<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

class PathReducerManager
{
    /**
     * @var PathReducerInterface[]
     */
    private $pathReducers;

    public function __construct(array $pathReducers = [])
    {
        $this->pathReducers = $pathReducers;
    }

    /**
     * Returns a list of available path reducers.
     *
     * @return array
     */
    public function getPathReducers(): array
    {
        return $this->pathReducers;
    }

    /**
     * Returns one path reducer by name
     *
     * @param $name
     * @return PathReducerInterface
     *
     * @throws \Exception
     */
    public function getPathReducer($name): PathReducerInterface
    {
        if (isset($this->pathReducers[$name])) {
            return $this->pathReducers[$name];
        }

        throw new \Exception(sprintf('Path reducer %s does not exist.', $name));
    }

    /**
     * Check if there is a path reducer by name
     *
     * @param $name
     * @return bool
     */
    public function hasPathReducer($name): bool
    {
        return isset($this->pathReducers[$name]);
    }
}

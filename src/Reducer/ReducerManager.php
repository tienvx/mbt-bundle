<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;

class ReducerManager
{
    /**
     * @var array
     */
    private $reducers = [];

    public function __construct(iterable $reducers)
    {
        foreach ($reducers as $reducer) {
            if ($reducer instanceof ReducerInterface && $reducer->support()) {
                $this->reducers[$reducer->getName()] = $reducer;
            }
        }
    }

    public function get(string $name): ReducerInterface
    {
        $reducer = $this->reducers[$name] ?? null;
        if ($reducer instanceof ReducerInterface) {
            return $reducer;
        }

        throw new Exception(sprintf('Path reducer "%s" does not exist.', $name));
    }

    public function has(string $name): bool
    {
        $reducer = $this->reducers[$name] ?? null;

        return $reducer instanceof ReducerInterface;
    }

    public function all(): array
    {
        return $this->reducers;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;

class ReducerManager
{
    /**
     * @var array
     */
    private $plugins;

    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function get(string $name): ReducerInterface
    {
        $reducer = $this->plugins[$name] ?? null;
        if ($reducer instanceof ReducerInterface) {
            return $reducer;
        }

        throw new Exception(sprintf('Path reducer "%s" does not exist.', $name));
    }

    public function has(string $name): bool
    {
        $reducer = $this->plugins[$name] ?? null;

        return $reducer instanceof ReducerInterface;
    }

    public function all(): array
    {
        return $this->plugins;
    }
}

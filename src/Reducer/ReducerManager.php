<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Plugin\PluginManager;

class ReducerManager extends PluginManager implements ReducerManagerInterface
{
    public function getReducer(string $name): ReducerInterface
    {
        return $this->get($name);
    }

    protected function getPluginInterface(): string
    {
        return ReducerInterface::class;
    }

    protected function getInvalidPluginExceptionMessage(string $name): string
    {
        return sprintf('Reducer "%s" does not exist.', $name);
    }
}

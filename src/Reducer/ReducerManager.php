<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class ReducerManager extends AbstractPluginManager implements ReducerManagerInterface
{
    public function getReducer(string $name): ReducerInterface
    {
        if ($this->has($name) && ($reducer = $this->get($name)) && $reducer instanceof ReducerInterface) {
            return $reducer;
        }

        throw new UnexpectedValueException(sprintf('Reducer "%s" does not exist.', $name));
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class ReducerManager extends AbstractPluginManager
{
    /**
     * @throws ExceptionInterface
     */
    public function get(string $name): ReducerInterface
    {
        $reducer = $this->locator->has($name) ? $this->locator->get($name) : null;
        if ($reducer instanceof ReducerInterface) {
            return $reducer;
        }

        throw new UnexpectedValueException(sprintf('Reducer "%s" does not exist.', $name));
    }
}

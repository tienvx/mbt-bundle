<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class GeneratorManager extends AbstractPluginManager
{
    /**
     * @throws ExceptionInterface
     */
    public function get(string $name): GeneratorInterface
    {
        $generator = $this->locator->has($name) ? $this->locator->get($name) : null;
        if ($generator instanceof GeneratorInterface) {
            return $generator;
        }

        throw new UnexpectedValueException(sprintf('Generator "%s" does not exist.', $name));
    }
}

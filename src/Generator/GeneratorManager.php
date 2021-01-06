<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class GeneratorManager extends AbstractPluginManager implements GeneratorManagerInterface
{
    public function getGenerator(string $name): GeneratorInterface
    {
        if ($this->has($name) && ($generator = $this->get($name)) && $generator instanceof GeneratorInterface) {
            return $generator;
        }

        throw new UnexpectedValueException(sprintf('Generator "%s" does not exist.', $name));
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;

class GeneratorManager
{
    /**
     * @var GeneratorInterface[]
     */
    private $generators;

    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
    }

    /**
     * Returns a list of available generators.
     *
     * @return array
     */
    public function getGenerators(): array
    {
        return $this->generators;
    }

    /**
     * Returns one generator by name
     *
     * @param $name
     * @return GeneratorInterface
     *
     * @throws Exception
     */
    public function getGenerator($name): GeneratorInterface
    {
        if (isset($this->generators[$name])) {
            return $this->generators[$name];
        }

        throw new Exception(sprintf('Generator %s does not exist.', $name));
    }

    /**
     * Check if there is a generator by name
     *
     * @param $name
     * @return bool
     */
    public function hasGenerator($name): bool
    {
        return isset($this->generators[$name]);
    }
}

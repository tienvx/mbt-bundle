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
     * Returns one generator by name.
     *
     * @throws Exception
     */
    public function get(string $name): GeneratorInterface
    {
        if (isset($this->generators[$name])) {
            return $this->generators[$name];
        }

        throw new Exception(sprintf('Generator "%s" does not exist.', $name));
    }

    /**
     * Check if there is a generator by name.
     */
    public function has(string $name): bool
    {
        return isset($this->generators[$name]);
    }

    /**
     * @return GeneratorInterface[]
     */
    public function all(): array
    {
        return $this->generators;
    }
}

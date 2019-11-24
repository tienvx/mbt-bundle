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
        $generator = $this->generators[$name] ?? null;
        if ($generator instanceof GeneratorInterface) {
            return $generator;
        }

        throw new Exception(sprintf('Generator "%s" does not exist.', $name));
    }

    public function has(string $name): bool
    {
        return isset($this->generators[$name]);
    }

    public function all(): array
    {
        return $this->generators;
    }
}

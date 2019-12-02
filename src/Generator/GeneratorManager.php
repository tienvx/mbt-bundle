<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;

class GeneratorManager
{
    /**
     * @var array
     */
    private $generators = [];

    public function __construct(iterable $generators)
    {
        foreach ($generators as $generator) {
            if ($generator instanceof GeneratorInterface && $generator->support()) {
                $this->generators[$generator->getName()] = $generator;
            }
        }
    }

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
        $generator = $this->generators[$name] ?? null;

        return $generator instanceof GeneratorInterface;
    }

    public function all(): array
    {
        return $this->generators;
    }
}

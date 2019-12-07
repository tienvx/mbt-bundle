<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;

class GeneratorManager
{
    /**
     * @var array
     */
    private $plugins;

    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function get(string $name): GeneratorInterface
    {
        $generator = $this->plugins[$name] ?? null;
        if ($generator instanceof GeneratorInterface) {
            return $generator;
        }

        throw new Exception(sprintf('Generator "%s" does not exist.', $name));
    }

    public function has(string $name): bool
    {
        $generator = $this->plugins[$name] ?? null;

        return $generator instanceof GeneratorInterface;
    }

    public function all(): array
    {
        return $this->plugins;
    }
}

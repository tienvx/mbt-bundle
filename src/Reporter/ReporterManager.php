<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;

class ReporterManager
{
    /**
     * @var array
     */
    private $plugins;

    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function get(string $name): ReporterInterface
    {
        $reporter = $this->plugins[$name] ?? null;
        if ($reporter instanceof ReporterInterface) {
            return $reporter;
        }

        throw new Exception(sprintf('Reporter "%s" does not exist.', $name));
    }

    public function has(string $name): bool
    {
        $reporter = $this->plugins[$name] ?? null;

        return $reporter instanceof ReporterInterface;
    }

    public function all(): array
    {
        return $this->plugins;
    }
}

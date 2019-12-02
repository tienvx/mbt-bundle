<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;

class ReporterManager
{
    /**
     * @var array
     */
    private $reporters = [];

    public function __construct(iterable $reporters)
    {
        foreach ($reporters as $reporter) {
            if ($reporter instanceof ReporterInterface && $reporter->support()) {
                $this->reporters[$reporter->getName()] = $reporter;
            }
        }
    }

    public function get(string $name): ReporterInterface
    {
        $reporter = $this->reporters[$name] ?? null;
        if ($reporter instanceof ReporterInterface) {
            return $reporter;
        }

        throw new Exception(sprintf('Reporter "%s" does not exist.', $name));
    }

    public function has(string $name): bool
    {
        $reporter = $this->reporters[$name] ?? null;

        return $reporter instanceof ReporterInterface;
    }

    public function all(): array
    {
        return $this->reporters;
    }
}

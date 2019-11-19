<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;

class ReporterManager
{
    /**
     * @var ReporterInterface[]
     */
    private $reporters;

    public function __construct(array $reporters = [])
    {
        $this->reporters = $reporters;
    }

    /**
     * Returns one reporter by name.
     *
     * @throws Exception
     */
    public function get(string $name): ReporterInterface
    {
        if (isset($this->reporters[$name])) {
            return $this->reporters[$name];
        }

        throw new Exception(sprintf('Reporter "%s" does not exist.', $name));
    }

    /**
     * Check if there is a reporter by name.
     */
    public function has(string $name): bool
    {
        return isset($this->reporters[$name]);
    }

    /**
     * @return ReporterInterface[]
     */
    public function all(): array
    {
        return $this->reporters;
    }
}

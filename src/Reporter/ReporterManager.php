<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

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
     * Returns a list of available reporters.
     *
     * @return array
     */
    public function getReporters(): array
    {
        return $this->reporters;
    }

    /**
     * Returns one reporter by name
     *
     * @param $name
     * @return ReporterInterface
     *
     * @throws \Exception
     */
    public function getReporter($name): ReporterInterface
    {
        if (isset($this->reporters[$name])) {
            return $this->reporters[$name];
        }

        throw new \Exception(sprintf('Reporter %s does not exist.', $name));
    }

    /**
     * Check if there is a reporter by name
     *
     * @param $name
     * @return bool
     */
    public function hasReporter($name): bool
    {
        return isset($this->reporters[$name]);
    }
}

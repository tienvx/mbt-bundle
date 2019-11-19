<?php

namespace Tienvx\Bundle\MbtBundle\PredefinedCase;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\PredefinedCase;

class PredefinedCaseManager
{
    /**
     * @var PredefinedCase[]
     */
    private $predefinedCases = [];

    public function add(PredefinedCase $predefinedCase)
    {
        $this->predefinedCases[$predefinedCase->getName()] = $predefinedCase;
    }

    /**
     * Returns one predefined case by name.
     *
     * @throws Exception
     */
    public function get(string $name): PredefinedCase
    {
        if (isset($this->predefinedCases[$name])) {
            return $this->predefinedCases[$name];
        }

        throw new Exception(sprintf('Predefined case "%s" does not exist.', $name));
    }

    /**
     * Check if there is a predefined case by name.
     */
    public function has(string $name): bool
    {
        return isset($this->predefinedCases[$name]);
    }

    /**
     * @return PredefinedCase[]
     */
    public function all(): array
    {
        return $this->predefinedCases;
    }
}

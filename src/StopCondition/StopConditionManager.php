<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

class StopConditionManager
{
    /**
     * @var StopConditionInterface[]
     */
    private $stopConditions;

    public function __construct(array $stopConditions = [])
    {
        $this->stopConditions = $stopConditions;
    }

    /**
     * Returns a list of available stop conditions.
     *
     * @return array
     */
    public function getStopConditions(): array
    {
        return $this->stopConditions;
    }

    /**
     * Returns one stop condition by name
     *
     * @param $name
     * @return StopConditionInterface
     *
     * @throws \Exception
     */
    public function getStopCondition($name): StopConditionInterface
    {
        if (isset($this->stopConditions[$name])) {
            return $this->stopConditions[$name];
        }

        throw new \Exception(sprintf('Stop condition %s does not exist.', $name));
    }

    /**
     * Check if there is a stop condition by name
     *
     * @param $name
     * @return bool
     */
    public function hasStopCondition($name): bool
    {
        return isset($this->stopConditions[$name]);
    }
}

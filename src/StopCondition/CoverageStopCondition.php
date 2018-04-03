<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

class CoverageStopCondition implements StopConditionInterface
{
    /**
     * @var int
     */
    protected $edgeCoverage = 100;

    /**
     * @var int
     */
    protected $vertexCoverage = 100;

    public function setArguments(array $arguments)
    {
        foreach ($arguments as $argument => $value) {
            if (in_array($argument, ['edgeCoverage', 'vertexCoverage'])) {
                if ($value >= 0 && $value <= 100) {
                    $this->{$argument} = $value;
                }
                else {
                    throw new \Exception(sprintf('Invalid coverage "%s".', $value));
                }
            }
            else {
                throw new \Exception(sprintf('Invalid argument "%s".', $argument));
            }
        }
    }

    public function meet(array $context): bool
    {
        return $this->edgeCoverage >= $context['edgeCoverage'] && $this->vertexCoverage >= $context['vertexCoverage'];
    }

    public static function getName()
    {
        return 'coverage';
    }
}

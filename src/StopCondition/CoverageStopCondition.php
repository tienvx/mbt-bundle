<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

class CoverageStopCondition extends MaxLengthStopCondition
{
    /**
     * @var int
     */
    protected $edgeCoverage = 100;

    /**
     * @var int
     */
    protected $vertexCoverage = 100;

    /**
     * @param array $arguments
     * @throws \Exception
     */
    public function setArguments(array $arguments)
    {
        foreach ($arguments as $argument => $value) {
            if (in_array($argument, ['edgeCoverage', 'vertexCoverage'])) {
                if ($value >= 0 && $value <= 100) {
                    $this->{$argument} = $value;
                } else {
                    throw new \Exception(sprintf('Invalid coverage "%s".', $value));
                }
            } else {
                throw new \Exception(sprintf('Invalid argument "%s".', $argument));
            }
        }
    }

    public function meet(array $context): bool
    {
        return ($context['edgeCoverage'] >= $this->edgeCoverage && $context['vertexCoverage'] >= $this->vertexCoverage) || parent::meet($context);
    }

    public static function getName()
    {
        return 'coverage';
    }
}

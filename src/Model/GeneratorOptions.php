<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class GeneratorOptions implements GeneratorOptionsInterface
{
    /**
     * @var int|null
     */
    protected $transitionCoverage;

    /**
     * @var int|null
     */
    protected $placeCoverage;

    /**
     * @var int|null
     */
    protected $maxSteps;

    public function getMaxSteps(): ?int
    {
        return $this->maxSteps;
    }

    public function setMaxSteps(?int $maxSteps): GeneratorOptionsInterface
    {
        $this->maxSteps = $maxSteps;

        return $this;
    }

    public function getPlaceCoverage(): ?int
    {
        return $this->placeCoverage;
    }

    public function setPlaceCoverage(?int $placeCoverage): GeneratorOptionsInterface
    {
        $this->placeCoverage = $placeCoverage;

        return $this;
    }

    public function getTransitionCoverage(): ?int
    {
        return $this->transitionCoverage;
    }

    public function setTransitionCoverage(?int $transitionCoverage): GeneratorOptionsInterface
    {
        $this->transitionCoverage = $transitionCoverage;

        return $this;
    }
}

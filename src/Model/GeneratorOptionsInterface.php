<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface GeneratorOptionsInterface
{
    public function getMaxSteps(): ?int;

    public function setMaxSteps(?int $maxSteps): self;

    public function getPlaceCoverage(): ?int;

    public function setPlaceCoverage(?int $placeCoverage): self;

    public function getTransitionCoverage(): ?int;

    public function setTransitionCoverage(?int $transitionCoverage): self;
}

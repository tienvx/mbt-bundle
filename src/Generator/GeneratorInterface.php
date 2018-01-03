<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;

interface GeneratorInterface
{
    public function canGoNextStep(Directed $currentEdge): bool;

    public function getNextStep(): ?Directed;

    public function goToNextStep(Directed $edge, bool $callSUT = false);

    public function getMaxProgress(): int;

    public function getCurrentProgress(): int;

    public function getCurrentProgressMessage(): string;

    public function meetStopCondition(): bool;
}

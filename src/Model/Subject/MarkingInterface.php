<?php

namespace Tienvx\Bundle\MbtBundle\Model\Subject;

interface MarkingInterface
{
    public function getMarking();

    public function setMarking($marking, array $context = []): void;
}

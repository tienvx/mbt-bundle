<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

interface SubjectMarkingInterface
{
    public function getMarking();

    public function setMarking($marking, array $context = []): void;
}

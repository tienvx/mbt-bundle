<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

interface SubjectInterface
{
    /**
     * @return string Model name
     */
    public static function support(): string;
}

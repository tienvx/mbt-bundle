<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

class Subject
{
    /**
     * @var boolean
     */
    protected $callSUT;

    public function __construct()
    {
        $this->callSUT = false;
    }

    /**
     * @param $callSUT boolean
     */
    public function setCallSUT($callSUT)
    {
        $this->callSUT = $callSUT;
    }
}

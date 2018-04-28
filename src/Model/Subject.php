<?php

namespace Tienvx\Bundle\MbtBundle\Model;

abstract class Subject
{
    /**
     * @var string Required by workflow component
     */
    public $marking;

    /**
     * @var boolean
     */
    protected $callSUT;

    /**
     * @var array
     */
    protected $data;

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

    /**
     * @param $data array
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}

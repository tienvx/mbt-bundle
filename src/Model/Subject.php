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
    public function setCallSUT(bool $callSUT)
    {
        $this->callSUT = $callSUT;
    }

    /**
     * @param $data array
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }
}

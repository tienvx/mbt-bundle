<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

abstract class Subject
{
    /**
     * @var string Required by workflow component
     */
    public $marking;

    /**
     * @var boolean
     */
    protected $testing = false;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $storedData = [];

    /**
     * @var boolean
     */
    protected $needData = true;

    /**
     * @param $testing boolean
     */
    public function setTesting(bool $testing = false)
    {
        $this->testing = $testing;
    }

    /**
     * @return boolean
     */
    public function isTesting()
    {
        return $this->testing;
    }

    /**
     * @param $needData boolean
     */
    public function setNeedData(bool $needData = true)
    {
        $this->needData = $needData;
    }

    /**
     * @return boolean
     */
    public function needData()
    {
        return $this->needData;
    }

    /**
     * @param array $data
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
        return $this->data;
    }

    public function storeData()
    {
        $this->storedData = $this->data;
    }

    /**
     * @return array
     */
    public function getStoredData(): array
    {
        return $this->storedData;
    }

    public function applyTransition(string $transitionName)
    {
        if (method_exists($this, $transitionName)) {
            call_user_func([$this, $transitionName]);
        }
    }

    public function enterPlace(array $places)
    {
        foreach ($places as $place) {
            if (method_exists($this, $place)) {
                call_user_func([$this, $place]);
            }
        }
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }
}

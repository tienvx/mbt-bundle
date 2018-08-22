<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Exception;

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
    protected $data;

    /**
     * @var array
     */
    protected $dataProviders;

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
     * @param $data array|null
     */
    public function setData(array $data = null)
    {
        $this->data = $data;
    }

    /**
     * @param $transitionName string
     * @return array
     * @throws Exception
     */
    public function provideData(string $transitionName): array
    {
        if (isset($this->dataProviders[$transitionName]) && is_callable($this->dataProviders[$transitionName])) {
            $data = $this->dataProviders[$transitionName]();
            if (!is_array($data)) {
                throw new Exception(sprintf('Data provider for transition %s must return array', $transitionName));
            }
        } else {
            $data = [];
        }
        $this->data = $data;
        return $data;
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

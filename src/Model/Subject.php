<?php

namespace Tienvx\Bundle\MbtBundle\Model;

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
    protected $callSUT;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $dataProviders;

    /**
     * @var boolean
     */
    protected $announcing;

    public function __construct()
    {
        $this->callSUT = false;
        $this->announcing = false;
    }

    /**
     * @param $callSUT boolean
     */
    public function setCallSUT(bool $callSUT)
    {
        $this->callSUT = $callSUT;
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
        }
        else {
            $data = [];
        }
        $this->data = $data;
        return $data;
    }

    public function __invoke(string $type, string $placeOrTransitionName)
    {
        if (method_exists($this, $placeOrTransitionName)) {
            call_user_func([$this, $placeOrTransitionName]);
        }
    }

    /**
     * @param $announcing boolean
     */
    public function setAnnouncing(bool $announcing)
    {
        $this->announcing = $announcing;
    }

    /**
     * @return boolean
     */
    public function isAnnouncing()
    {
        return $this->announcing;
    }
}

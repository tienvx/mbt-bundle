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
    protected $generatingSteps;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $dataProviders;

    /**
     * @param $generatingSteps boolean
     */
    public function __construct(bool $generatingSteps = false)
    {
        $this->generatingSteps = $generatingSteps;
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

    public function applyTransition(string $transitionName)
    {
        if (method_exists($this, $transitionName)) {
            call_user_func([$this, $transitionName]);
        }
    }

    public function enterPlace(string $placeName)
    {
        if (method_exists($this, $placeName)) {
            call_user_func([$this, $placeName]);
        }
    }
}

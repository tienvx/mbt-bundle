<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Exception;

class Step
{
    /**
     * @var string|null
     */
    protected $transition;

    /**
     * @var StepData|null
     */
    protected $data;

    /**
     * @var string[]
     */
    protected $places;

    public function __construct(?string $transition, ?StepData $data, array $places)
    {
        $this->transition = $transition;
        $this->data = $data;
        $this->places = $places;
    }

    public function getTransition(): ?string
    {
        return $this->transition;
    }

    public function getData(): ?StepData
    {
        return $this->data;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    public function setTransition(?string $transition)
    {
        $this->transition = $transition;
    }

    public function setData(?StepData $data)
    {
        $this->data = $data;
    }

    public function setPlaces(array $places)
    {
        $this->places = $places;
    }

    /**
     * @return array
     */
    public function normalize(): array
    {
        return [
            'transition' => $this->transition,
            'data' => $this->data ? $this->data->normalize() : null,
            'places' => $this->places,
        ];
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return json_encode($this->normalize());
    }

    /**
     * @param array $step
     *
     * @return Step
     *
     * @throws Exception
     */
    public static function denormalize(array $step): Step
    {
        return new Step(
            $step['transition'],
            is_array($step['data']) ? StepData::denormalize($step['data']) : null,
            is_array($step['places']) ? $step['places'] : []
        );
    }

    /**
     * @param string $step
     *
     * @return Step
     *
     * @throws Exception
     */
    public static function deserialize(string $step): Step
    {
        return self::denormalize(json_decode($step, true));
    }
}

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
     * @var Data
     */
    protected $data;

    /**
     * @var string[]
     */
    protected $places;

    public function __construct(?string $transition, Data $data, array $places = [])
    {
        $this->transition = $transition;
        $this->data = $data;
        $this->places = $places;
    }

    public function getTransition(): ?string
    {
        return $this->transition;
    }

    public function getData(): Data
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

    public function setData(Data $data)
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
            'data' => $this->data->normalize(),
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
            Data::denormalize($step['data']),
            !empty($step['places']) && is_array($step['places']) ? $step['places'] : []
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

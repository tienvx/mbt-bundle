<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

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

    public function setPlaces(array $places): void
    {
        $this->places = $places;
    }

    public function normalize(): array
    {
        return [
            'transition' => $this->transition,
            'data' => $this->data->normalize(),
            'places' => $this->places,
        ];
    }

    public function serialize(): string
    {
        return json_encode($this->normalize());
    }

    /**
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
     * @throws Exception
     */
    public static function deserialize(string $step): Step
    {
        return self::denormalize(json_decode($step, true));
    }
}

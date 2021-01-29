<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class Revision implements RevisionInterface
{
    protected ?int $id;
    protected ?ModelInterface $model = null;
    protected array $places = [];
    protected array $transitions = [];

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?ModelInterface
    {
        return $this->model;
    }

    public function setModel(?ModelInterface $model): void
    {
        $this->model = $model;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    public function setPlaces(PlaceInterface ...$places): void
    {
        $this->places = [];
        foreach ($places as $place) {
            $this->addPlace($place);
        }
    }

    public function addPlace(PlaceInterface $place): void
    {
        $this->places[] = $place;
    }

    public function getPlace(int $index): ?PlaceInterface
    {
        return $this->places[$index] ?? null;
    }

    public function getTransitions(): array
    {
        return $this->transitions;
    }

    public function setTransitions(TransitionInterface ...$transitions): void
    {
        $this->transitions = [];
        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }
    }

    public function addTransition(TransitionInterface $transition): void
    {
        $this->transitions[] = $transition;
    }

    public function getTransition(int $index): ?TransitionInterface
    {
        return $this->transitions[$index] ?? null;
    }

    public function toArray(): array
    {
        return [
            'places' => array_map(fn (PlaceInterface $place) => $place->toArray(), $this->places),
            'transitions' => array_map(
                fn (TransitionInterface $transition) => $transition->toArray(),
                $this->transitions
            ),
        ];
    }
}

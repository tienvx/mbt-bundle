<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;

class Model implements ModelInterface
{
    protected ?int $id;

    protected string $label;

    protected string $tags = '';

    protected array $places = [];

    protected array $transitions = [];

    protected int $version;

    protected DateTimeInterface $updatedAt;

    protected DateTimeInterface $createdAt;

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getTags(): string
    {
        return $this->tags;
    }

    public function setTags(string $tags): void
    {
        $this->tags = '';
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    public function setPlaces(array $places): void
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

    public function setTransitions(array $transitions): void
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

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}

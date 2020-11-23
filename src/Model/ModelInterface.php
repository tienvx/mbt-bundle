<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;

interface ModelInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getTags(): array;

    public function setTags(array $tags): void;

    public function addTag(string $tag): void;

    public function getPlaces(): array;

    public function setPlaces(array $places): void;

    public function addPlace(PlaceInterface $place): void;

    public function getPlace(int $index): ?PlaceInterface;

    public function getTransitions(): array;

    public function setTransitions(array $transitions): void;

    public function addTransition(TransitionInterface $transition): void;

    public function getTransition(int $index): ?TransitionInterface;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getVersion(): int;

    public function setVersion(int $version): void;

    public function getCreatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): ?DateTimeInterface;
}

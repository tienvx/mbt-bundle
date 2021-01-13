<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;

abstract class Model implements ModelInterface
{
    protected ?int $id;
    protected ?int $author;
    protected string $label = '';
    protected ?string $tags = null;
    protected string $startUrl = '';
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

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function setAuthor(?int $author): void
    {
        $this->author = $author;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): void
    {
        $this->tags = $tags;
    }

    public function getStartUrl(): string
    {
        return $this->startUrl;
    }

    public function setStartUrl(string $startUrl): void
    {
        $this->startUrl = $startUrl;
    }

    abstract public function getPlaces(): array;

    abstract public function setPlaces(array $places): void;

    abstract public function getPlace(int $index): ?PlaceInterface;

    abstract public function getTransitions(): array;

    abstract public function setTransitions(array $transitions): void;

    abstract public function getTransition(int $index): ?TransitionInterface;

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

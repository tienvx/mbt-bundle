<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class Transition implements TransitionInterface
{
    use HasCommands;

    protected string $label = '';
    protected ?string $guard = null;
    protected array $fromPlaces = [];
    protected array $toPlaces = [];

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getGuard(): ?string
    {
        return $this->guard;
    }

    public function setGuard(?string $guard): void
    {
        $this->guard = $guard;
    }

    public function getFromPlaces(): array
    {
        return $this->fromPlaces;
    }

    public function setFromPlaces(array $fromPlaces): void
    {
        $this->fromPlaces = [];

        foreach ($fromPlaces as $fromPlace) {
            $this->addFromPlace($fromPlace);
        }
    }

    public function addFromPlace(int $fromPlace): void
    {
        $this->fromPlaces[] = $fromPlace;
    }

    public function getToPlaces(): array
    {
        return $this->toPlaces;
    }

    public function setToPlaces(array $toPlaces): void
    {
        $this->toPlaces = [];

        foreach ($toPlaces as $toPlace) {
            $this->addToPlace($toPlace);
        }
    }

    public function addToPlace(int $toPlace): void
    {
        $this->toPlaces[] = $toPlace;
    }

    public function isSame(TransitionInterface $transition): bool
    {
        return $this->getGuard() === $transition->getGuard() &&
            $this->getFromPlaces() === $transition->getFromPlaces() &&
            $this->getToPlaces() === $transition->getToPlaces() &&
            $this->isSameCommands($transition->getCommands());
    }
}

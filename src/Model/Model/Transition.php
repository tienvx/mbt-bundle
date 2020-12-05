<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class Transition implements TransitionInterface
{
    protected string $label = '';
    protected ?string $guard = null;
    protected array $actions = [];
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

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): void
    {
        $this->actions = [];

        foreach ($actions as $action) {
            $this->addAction($action);
        }
    }

    public function addAction(CommandInterface $action): void
    {
        $this->actions[] = $action;
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

    public function addToPlace(ToPlaceInterface $toPlace): void
    {
        $this->toPlaces[] = $toPlace;
    }

    public function isSame(TransitionInterface $transition): bool
    {
        return $this->getGuard() === $transition->getGuard() &&
            $this->getFromPlaces() === $transition->getFromPlaces() &&
            $this->isSameToPlaces($transition->getToPlaces()) &&
            $this->isSameActions($transition->getActions());
    }

    protected function isSameActions(array $actions): bool
    {
        if (count($this->actions) !== count($actions)) {
            return false;
        }
        foreach ($actions as $index => $action) {
            $selfAction = $this->actions[$index] ?? null;
            if (
                !$selfAction instanceof CommandInterface ||
                !$action instanceof CommandInterface ||
                !$selfAction->isSame($action)
            ) {
                return false;
            }
        }

        return true;
    }

    protected function isSameToPlaces(array $toPlaces): bool
    {
        if (count($this->toPlaces) !== count($toPlaces)) {
            return false;
        }
        foreach ($toPlaces as $index => $toPlace) {
            $selfToPlace = $this->toPlaces[$index] ?? null;
            if (
                !$selfToPlace instanceof ToPlaceInterface ||
                !$toPlace instanceof ToPlaceInterface ||
                !$selfToPlace->isSame($toPlace)
            ) {
                return false;
            }
        }

        return true;
    }
}

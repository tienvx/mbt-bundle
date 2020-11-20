<?php

namespace Tienvx\Bundle\MbtBundle\Model\Selenium;

use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

class Command implements CommandInterface
{
    protected string $command;
    protected string $target;
    protected ?string $value;
    protected PlaceInterface $place;
    protected TransitionInterface $transition;

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function setPlace(PlaceInterface $place)
    {
        $this->place = $place;
    }

    public function getPlace(): PlaceInterface
    {
        return $this->place;
    }

    public function setTransition(TransitionInterface $transition)
    {
        $this->transition = $transition;
    }

    public function getTransition(): TransitionInterface
    {
        return $this->transition;
    }
}

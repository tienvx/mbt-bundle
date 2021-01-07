<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class Place implements PlaceInterface
{
    protected string $label = '';
    protected bool $start = false;
    protected array $assertions = [];

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getStart(): bool
    {
        return $this->start;
    }

    public function setStart(bool $start): void
    {
        $this->start = $start;
    }

    public function getAssertions(): array
    {
        return $this->assertions;
    }

    public function setAssertions(array $assertions): void
    {
        $this->assertions = [];

        foreach ($assertions as $assertion) {
            $this->addAssertion($assertion);
        }
    }

    public function addAssertion(CommandInterface $assertion): void
    {
        $this->assertions[] = $assertion;
    }

    public function isSame(PlaceInterface $place): bool
    {
        return $this->getStart() === $place->getStart() && $this->isSameAssertions($place->getAssertions());
    }

    protected function isSameAssertions(array $assertions): bool
    {
        if (count($this->assertions) !== count($assertions)) {
            return false;
        }
        foreach ($assertions as $index => $assertion) {
            $selfAssertion = $this->assertions[$index] ?? null;
            if (
                !$selfAssertion instanceof CommandInterface ||
                !$assertion instanceof CommandInterface ||
                !$selfAssertion->isSame($assertion)
            ) {
                return false;
            }
        }

        return true;
    }
}

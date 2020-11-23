<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class Place implements PlaceInterface
{
    protected string $label;
    protected bool $int = false;
    protected array $assertions = [];

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getInit(): bool
    {
        return $this->int;
    }

    public function setInit(bool $init): void
    {
        $this->int = $init;
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
}

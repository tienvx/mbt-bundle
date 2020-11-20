<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Petrinet\Model\Place as BasePlace;
use Tienvx\Bundle\MbtBundle\Model\Selenium\CommandInterface;

class Place extends BasePlace implements PlaceInterface
{
    protected string $label = '';

    protected Collection $assertions;

    protected PetrinetInterface $petrinet;

    public function __construct()
    {
        parent::__construct();
        $this->assertions = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getAssertions(): Collection
    {
        return $this->assertions;
    }

    public function setAssertions(iterable $assertions): void
    {
        $this->assertions = new ArrayCollection();

        foreach ($assertions as $assertion) {
            $this->addAssertion($assertion);
        }
    }

    /**
     * Adds a step.
     */
    public function addAssertion(CommandInterface $assertion)
    {
        $this->assertions[] = $assertion;
    }

    /**
     * Tells if the Steps has the given step.
     *
     * @return bool
     */
    public function hasAssertion(CommandInterface $assertion)
    {
        return $this->assertions->contains($assertion);
    }

    /**
     * Removes a step.
     */
    public function removeAssertion(CommandInterface $assertion)
    {
        $this->assertions->removeElement($assertion);
    }

    public function setPetrinet(PetrinetInterface $petrinet): void
    {
        $this->petrinet = $petrinet;
    }

    public function getPetrinet(): PetrinetInterface
    {
        return $this->petrinet;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SingleColorPetrinet\Model\GuardedTransition as BaseTransition;
use Tienvx\Bundle\MbtBundle\Model\Selenium\CommandInterface;

class Transition extends BaseTransition implements TransitionInterface
{
    protected string $label = '';

    protected Collection $actions;

    protected PetrinetInterface $petrinet;

    public function __construct()
    {
        parent::__construct();
        $this->actions = new ArrayCollection();
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

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function setActions(iterable $actions): void
    {
        $this->actions = new ArrayCollection();

        foreach ($actions as $action) {
            $this->addAction($action);
        }
    }

    public function addAction(CommandInterface $action): void
    {
        $this->actions[] = $action;
    }

    public function removeAction(CommandInterface $action): void
    {
        $this->actions->removeElement($action);
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

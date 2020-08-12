<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use SingleColorPetrinet\Model\GuardedTransition as BaseTransition;
use Tienvx\Bundle\MbtBundle\Model\Selenium\CommandInterface;

class Transition extends BaseTransition implements TransitionInterface
{
    protected string $label = '';

    protected ArrayCollection $actions;

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

    public function getActions(): ArrayCollection
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

    /**
     * Adds a step.
     */
    public function addAction(CommandInterface $action)
    {
        $this->actions[] = $action;
    }

    /**
     * Tells if the Steps has the given step.
     *
     * @return bool
     */
    public function hasAction(CommandInterface $action)
    {
        return $this->actions->contains($action);
    }

    /**
     * Removes a step.
     */
    public function removeAction(CommandInterface $action)
    {
        $this->actions->removeElement($action);
    }
}

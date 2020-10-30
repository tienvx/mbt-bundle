<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Doctrine\Common\Collections\ArrayCollection;

class Steps implements StepsInterface
{
    protected ArrayCollection $steps;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    public function getSteps(): ArrayCollection
    {
        return $this->steps;
    }

    /**
     * Adds a step.
     */
    public function addStep(StepInterface $step)
    {
        $this->steps[] = $step;
    }

    /**
     * Tells if the Steps has the given step.
     *
     * @return bool
     */
    public function hasStep(StepInterface $step)
    {
        return $this->steps->contains($step);
    }

    /**
     * Removes a step.
     */
    public function removeStep(StepInterface $step)
    {
        $this->steps->removeElement($step);
    }

    /**
     * {@inheritdoc}
     */
    public function setSteps($steps): void
    {
        $this->steps = new ArrayCollection();

        foreach ($steps as $step) {
            $this->addStep($step);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLength(): int
    {
        return $this->steps->count();
    }
}

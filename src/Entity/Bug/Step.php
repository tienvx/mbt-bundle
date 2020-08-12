<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Bug;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step as StepModel;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

/**
 * @ORM\Entity
 */
class Step extends StepModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * @ORM\OneToMany(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Marking")
     * @ORM\JoinTable(
     *  name="step_marking_xref",
     *  joinColumns={@ORM\JoinColumn(name="step_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="place_id", unique=true)}
     * )
     */
    protected MarkingInterface $marking;

    /**
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition", nullable=true)
     */
    protected ?TransitionInterface $transition = null;
}

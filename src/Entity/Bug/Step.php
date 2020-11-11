<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Bug;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step as StepModel;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
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
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Bug", inversedBy="steps")
     */
    protected BugInterface $bug;

    /**
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Marking")
     * @ORM\JoinColumn(name="marking_id", referencedColumnName="id", nullable=false)
     */
    protected MarkingInterface $marking;

    /**
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition")
     * @ORM\JoinColumn(name="transition_id", referencedColumnName="id", nullable=true)
     */
    protected ?TransitionInterface $transition = null;
}

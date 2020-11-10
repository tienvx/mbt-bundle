<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Bug;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Bug\Steps as StepsModel;

/**
 * @ORM\Entity
 */
class Steps extends StepsModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Bug\Step",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     * @ORM\JoinTable(
     *  name="steps_step_xref",
     *  joinColumns={@ORM\JoinColumn(name="steps_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="step_id", unique=true)}
     * )
     */
    protected ArrayCollection $steps;
}

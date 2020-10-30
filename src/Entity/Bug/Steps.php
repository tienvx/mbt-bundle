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
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Bug\Steps",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected ArrayCollection $steps;
}

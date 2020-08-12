<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model as BaseModel;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="model")
 */
class Model extends BaseModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     */
    protected string $label;

    /**
     * @ORM\Column(type="array")
     */
    protected array $tags = [];

    /**
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet", nullable=false, cascade={"persist", "remove"})
     * @Assert\Valid
     */
    protected PetrinetInterface $petrinet;
}

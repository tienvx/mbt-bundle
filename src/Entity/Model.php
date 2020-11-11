<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
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
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="petrinet_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid
     */
    protected PetrinetInterface $petrinet;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $updatedAt = null;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }
}

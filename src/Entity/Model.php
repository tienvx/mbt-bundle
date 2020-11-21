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
 * @ORM\HasLifecycleCallbacks
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
     * @Assert\NotBlank
     */
    protected string $label;

    /**
     * @ORM\Column(type="array")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    protected array $tags = [];

    /**
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="petrinet_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid
     */
    protected PetrinetInterface $petrinet;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $version;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new DateTime());
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model as BaseModel;

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
     * @ORM\Column(type="simple_array")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    protected array $tags = [];

    /**
     * @ORM\Column(type="array")
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\Entity\Model\Place"),
     *     @Assert\Valid
     * })
     */
    protected array $places = [];

    /**
     * @ORM\Column(type="array")
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\Entity\Model\Transition"),
     *     @Assert\Valid
     * })
     */
    protected array $transitions = [];

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

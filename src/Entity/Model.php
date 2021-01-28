<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model as BaseModel;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Validator\Tags;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $author;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $label = '';

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Tags
     */
    protected ?string $tags = null;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected DateTimeInterface $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Model\Revision")
     * @Assert\Valid
     */
    protected RevisionInterface $activeRevision;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Tienvx\Bundle\MbtBundle\Entity\Model\Revision",
     *     inversedBy="model"
     * )
     * @Assert\Valid
     */
    protected Collection $revisions;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
    }

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

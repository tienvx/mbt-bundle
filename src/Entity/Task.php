<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\Task as TaskModel;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @MbtAssert\StopConditions
 */
class Task extends TaskModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $title;

    /**
     * @ORM\OneToOne(targetEntity="Model")
     */
    protected ModelInterface $model;

    /**
     * @ORM\Embedded(class="Progress")
     */
    protected ProgressInterface $progress;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        parent::__construct();
        $this->progress = new Progress();
    }

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

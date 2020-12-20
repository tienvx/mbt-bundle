<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfigInterface;
use Tienvx\Bundle\MbtBundle\Model\Task as TaskModel;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Entity\Task\TaskConfig;
use Tienvx\Bundle\MbtBundle\Model\Task\TaskConfigInterface;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\ManyToOne(targetEntity="Model")
     * @ORM\JoinColumn(
     *     name="modelId",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    protected ModelInterface $model;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $author;

    /**
     * @ORM\Embedded(class="\Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig")
     * @Assert\Valid
     */
    protected SeleniumConfigInterface $seleniumConfig;

    /**
     * @ORM\Embedded(class="\Tienvx\Bundle\MbtBundle\Entity\Task\TaskConfig")
     * @Assert\Valid
     */
    protected TaskConfigInterface $taskConfig;

    /**
     * @ORM\Embedded(class="Progress")
     */
    protected ProgressInterface $progress;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected DateTimeInterface $updatedAt;

    public function __construct()
    {
        parent::__construct();
        $this->progress = new Progress();
        $this->seleniumConfig = new SeleniumConfig();
        $this->taskConfig = new TaskConfig();
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

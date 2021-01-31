<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Entity\Task\TaskConfig;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\Task as TaskModel;
use Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfigInterface;
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
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $title = '';

    /**
     * @ORM\OneToOne(targetEntity="\Tienvx\Bundle\MbtBundle\Entity\Model\Revision")
     * @Assert\Valid
     */
    protected RevisionInterface $modelRevision;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $author = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $running = false;

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
     * @ORM\OneToMany(targetEntity="\Tienvx\Bundle\MbtBundle\Entity\Bug", mappedBy="task", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected Collection $bugs;

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
        $this->bugs = new ArrayCollection();
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

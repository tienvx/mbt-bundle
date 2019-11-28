<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Bug
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @MbtAssert\BugStatus
     */
    private $status = BugWorkflow::NEW;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    private $steps;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $model;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $modelHash;

    /**
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $task;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $bugMessage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type("integer")
     */
    private $messagesCount = 0;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSteps(): Steps
    {
        return Steps::deserialize($this->steps);
    }

    public function setSteps(Steps $steps): void
    {
        $this->steps = $steps->serialize();
    }

    /**
     * @MbtAssert\Model
     */
    public function getModel(): Model
    {
        return new Model($this->model);
    }

    public function setModel(Model $model): void
    {
        $this->model = $model->getName();
    }

    public function getModelHash(): string
    {
        return $this->modelHash;
    }

    public function setModelHash(string $modelHash): void
    {
        $this->modelHash = $modelHash;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): void
    {
        $this->task = $task;
    }

    public function getBugMessage(): string
    {
        return $this->bugMessage;
    }

    public function setBugMessage(string $bugMessage): void
    {
        $this->bugMessage = $bugMessage;
    }

    public function getMessagesCount(): int
    {
        return $this->messagesCount;
    }

    public function setMessagesCount(int $messagesCount): void
    {
        $this->messagesCount = $messagesCount;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(new DateTime());
        }

        if (!$this->getUpdatedAt()) {
            $this->setUpdatedAt(new DateTime());
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        if (!$this->getUpdatedAt()) {
            $this->setUpdatedAt(new DateTime());
        }
    }
}

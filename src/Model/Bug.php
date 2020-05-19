<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class Bug implements BugInterface
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $steps;

    /**
     * @var string
     */
    protected $workflow;

    /**
     * @var string
     */
    protected $workflowHash;

    /**
     * @var TaskInterface
     */
    protected $task;

    /**
     * @var string
     */
    protected $bugMessage;

    /**
     * @var int
     */
    protected $messagesCount = 0;

    /**
     * @var ?DateTimeInterface
     */
    protected $updatedAt;

    /**
     * @var ?DateTimeInterface
     */
    protected $createdAt;

    public function __construct()
    {
        $this->status = BugWorkflow::NEW;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): BugInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): BugInterface
    {
        $this->status = $status;

        return $this;
    }

    public function getSteps(): Steps
    {
        return Steps::deserialize($this->steps);
    }

    public function setSteps(Steps $steps): BugInterface
    {
        $this->steps = $steps->serialize();

        return $this;
    }

    public function getWorkflow(): WorkflowInterface
    {
        return new Workflow($this->workflow);
    }

    public function setWorkflow(WorkflowInterface $workflow): BugInterface
    {
        $this->workflow = $workflow->getName();

        return $this;
    }

    public function getWorkflowHash(): string
    {
        return $this->workflowHash;
    }

    public function setWorkflowHash(string $workflowHash): BugInterface
    {
        $this->workflowHash = $workflowHash;

        return $this;
    }

    public function getTask(): ?TaskInterface
    {
        return $this->task;
    }

    public function setTask(?TaskInterface $task): BugInterface
    {
        $this->task = $task;

        return $this;
    }

    public function getBugMessage(): string
    {
        return $this->bugMessage;
    }

    public function setBugMessage(string $bugMessage): BugInterface
    {
        $this->bugMessage = $bugMessage;

        return $this;
    }

    public function getMessagesCount(): int
    {
        return $this->messagesCount;
    }

    public function setMessagesCount(int $messagesCount): BugInterface
    {
        $this->messagesCount = $messagesCount;

        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): BugInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): BugInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}

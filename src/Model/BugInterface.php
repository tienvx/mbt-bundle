<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

interface BugInterface
{
    public function getId(): ?int;

    public function getTitle(): string;

    public function setTitle(string $title): self;

    public function getStatus(): string;

    public function setStatus(string $status): self;

    public function getSteps(): Steps;

    public function setSteps(Steps $steps): self;

    public function getWorkflow(): WorkflowInterface;

    public function setWorkflow(WorkflowInterface $workflow): self;

    public function getWorkflowHash(): string;

    public function setWorkflowHash(string $workflowHash): self;

    public function getTask(): ?TaskInterface;

    public function setTask(?TaskInterface $task): self;

    public function getBugMessage(): string;

    public function setBugMessage(string $bugMessage): self;

    public function getMessagesCount(): int;

    public function setMessagesCount(int $messagesCount): self;

    public function setCreatedAt(DateTimeInterface $createdAt): self;

    public function getCreatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): self;

    public function getUpdatedAt(): ?DateTimeInterface;
}

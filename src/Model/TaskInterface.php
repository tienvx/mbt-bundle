<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfigInterface;
use Tienvx\Bundle\MbtBundle\Model\Task\TaskConfigInterface;

interface TaskInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getModel(): ModelInterface;

    public function setModel(ModelInterface $model): void;

    public function getAuthor(): ?int;

    public function setAuthor(?int $author): void;

    public function getSeleniumConfig(): SeleniumConfigInterface;

    public function setSeleniumConfig(SeleniumConfigInterface $seleniumConfig): void;

    public function getTaskConfig(): TaskConfigInterface;

    public function setTaskConfig(TaskConfigInterface $taskConfig): void;

    public function getProgress(): ProgressInterface;

    public function setProgress(ProgressInterface $progress): void;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getCreatedAt(): DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): DateTimeInterface;
}

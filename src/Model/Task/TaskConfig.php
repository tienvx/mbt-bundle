<?php

namespace Tienvx\Bundle\MbtBundle\Model\Task;

class TaskConfig implements TaskConfigInterface
{
    protected string $generator = '';
    protected array $generatorConfig = [];
    protected string $reducer = '';
    protected bool $notifyAuthor;
    protected array $notifyChannels = [];

    public function getGenerator(): string
    {
        return $this->generator;
    }

    public function setGenerator(string $generator): void
    {
        $this->generator = $generator;
    }

    public function getGeneratorConfig(): array
    {
        return $this->generatorConfig;
    }

    public function setGeneratorConfig(array $generatorConfig): void
    {
        $this->generatorConfig = $generatorConfig;
    }

    public function getReducer(): string
    {
        return $this->reducer;
    }

    public function setReducer(string $reducer): void
    {
        $this->reducer = $reducer;
    }

    public function getNotifyAuthor(): bool
    {
        return $this->notifyAuthor;
    }

    public function setNotifyAuthor(bool $notifyAuthor): void
    {
        $this->notifyAuthor = $notifyAuthor;
    }

    public function getNotifyChannels(): array
    {
        return $this->notifyChannels;
    }

    public function setNotifyChannels(array $notifyChannels): void
    {
        $this->notifyChannels = $notifyChannels;
    }
}

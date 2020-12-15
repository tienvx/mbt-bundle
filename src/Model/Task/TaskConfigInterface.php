<?php

namespace Tienvx\Bundle\MbtBundle\Model\Task;

interface TaskConfigInterface
{
    public function getGenerator(): string;

    public function setGenerator(string $generator): void;

    public function getGeneratorConfig(): array;

    public function setGeneratorConfig(array $generatorConfig): void;

    public function getReducer(): string;

    public function setReducer(string $reducer): void;

    public function getSendEmail(): bool;

    public function setSendEmail(bool $sendEmail): void;

    public function getNotifyChannels(): array;

    public function setNotifyChannels(array $notifyChannels): void;
}

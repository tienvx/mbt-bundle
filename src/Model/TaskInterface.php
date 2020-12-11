<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface TaskInterface
{
    public function setId(int $id);

    public function getId(): ?int;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getModel(): ModelInterface;

    public function setModel(ModelInterface $model): void;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): void;

    public function getSendEmail(): bool;

    public function setSendEmail(bool $sendEmail): void;

    public function getProvider(): string;

    public function setProvider(string $provider): void;

    public function getPlatform(): string;

    public function setPlatform(string $operatingSystem): void;

    public function getBrowser(): string;

    public function setBrowser(string $browser): void;

    public function getBrowserVersion(): string;

    public function setBrowserVersion(string $browserVersion): void;

    public function getResolution(): string;

    public function setResolution(string $resolution): void;

    public function getProgress(): ProgressInterface;

    public function setProgress(ProgressInterface $progress): void;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getCreatedAt(): DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): DateTimeInterface;
}

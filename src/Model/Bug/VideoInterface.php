<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

interface VideoInterface
{
    public function isRecording(): bool;

    public function setRecording(bool $recording): void;

    public function getErrorMessage(): ?string;

    public function setErrorMessage(?string $errorMessage = null): void;
}

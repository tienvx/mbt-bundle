<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

class Video implements VideoInterface
{
    protected bool $recording = false;
    protected ?string $errorMessage = null;

    public function isRecording(): bool
    {
        return $this->recording;
    }

    public function setRecording(bool $recording): void
    {
        $this->recording = $recording;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage = null): void
    {
        $this->errorMessage = $errorMessage;
    }
}

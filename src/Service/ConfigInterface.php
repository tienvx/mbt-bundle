<?php

namespace Tienvx\Bundle\MbtBundle\Service;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getGenerator(): string;

    /**
     * @return string
     */
    public function getReducer(): string;

    /**
     * @return bool
     */
    public function shouldNotifyAuthor(): bool;

    /**
     * @return array
     */
    public function getNotifyChannels(): array;

    /**
     * @return int
     */
    public function getMaxSteps(): int;
}

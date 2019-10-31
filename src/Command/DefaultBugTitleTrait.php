<?php

namespace Tienvx\Bundle\MbtBundle\Command;

trait DefaultBugTitleTrait
{
    /**
     * @var string
     */
    private $defaultBugTitle;

    public function setDefaultBugTitle(string $defaultBugTitle)
    {
        $this->defaultBugTitle = $defaultBugTitle;
    }
}

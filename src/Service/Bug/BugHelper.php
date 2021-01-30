<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

class BugHelper implements BugHelperInterface
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function createBug(array $steps, string $message, int $taskId): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle($this->translator->trans('mbt.default_bug_title', ['%id%' => $taskId]));
        $bug->setSteps($steps);
        $bug->setMessage($message);

        return $bug;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class BugHelper implements BugHelperInterface
{
    protected TranslatorInterface $translator;
    protected string $adminUrl;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function setAdminUrl(string $adminUrl): void
    {
        $this->adminUrl = $adminUrl;
    }

    public function create(array $steps, string $message, TaskInterface $task): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle($this->translator->trans('mbt.default_bug_title', ['model' => $task->getModel()->getLabel()]));
        $bug->setSteps($steps);
        $bug->setMessage($message);
        $bug->setTask($task);
        $bug->setModelVersion($task->getModel()->getVersion());

        return $bug;
    }

    public function buildBugUrl(BugInterface $bug): string
    {
        return sprintf("{$this->adminUrl}/bug/%d", $bug->getId());
    }
}

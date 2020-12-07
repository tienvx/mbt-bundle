<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

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

    public function create(array $steps, string $message, ModelInterface $model): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle($this->translator->trans('mbt.default_bug_title', ['model' => $model->getLabel()]));
        $bug->setSteps($steps);
        $bug->setMessage($message);
        $bug->setModel($model);
        $bug->setModelVersion($model->getVersion());

        return $bug;
    }

    public function buildBugUrl(BugInterface $bug): string
    {
        return sprintf("{$this->adminUrl}/bug/%d", $bug->getId());
    }
}

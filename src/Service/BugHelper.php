<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepsInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class BugHelper implements BugHelperInterface
{
    protected EntityManagerInterface $entityManager;
    protected ConfigLoaderInterface $configLoader;
    protected TranslatorInterface $translator;
    protected string $bugUrl;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfigLoaderInterface $configLoader,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->configLoader = $configLoader;
        $this->translator = $translator;
    }

    public function setBugUrl(string $bugUrl): void
    {
        $this->bugUrl = $bugUrl;
    }

    public function create(StepsInterface $steps, string $message, ModelInterface $model): void
    {
        // Executing task take long time. Reconnect database to create bug.
        $this->entityManager->getConnection()->connect();

        $bug = new Bug();
        $bug->setTitle($this->translator->trans('mbt.default_bug_title', ['model' => $model->getLabel()]));
        $bug->setSteps($steps);
        $bug->setMessage($message);
        $bug->setModel($model);

        $this->entityManager->persist($bug);
        $this->entityManager->flush();
    }

    public function buildBugUrl(BugInterface $bug): string
    {
        return sprintf($this->bugUrl, $bug->getId());
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Message\ReproducePathMessage;
use Tienvx\Bundle\MbtBundle\PathReducer\QueuedPathReducerInterface;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;

class ReproducePathMessageHandler implements MessageHandlerInterface
{
    private $reducerManager;
    private $entityManager;

    public function __construct(PathReducerManager $reducerManager, EntityManagerInterface $entityManager)
    {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ReproducePathMessage $reproducePathMessage
     * @throws \Exception
     */
    public function __invoke(ReproducePathMessage $reproducePathMessage)
    {
        $reproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($reproducePathMessage->getId());

        if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
            return;
        }

        if ($this->reducerManager->hasPathReducer($reproducePath->getReducer())) {
            $reducer = $this->reducerManager->getPathReducer($reproducePath->getReducer());
            if ($reducer instanceof QueuedPathReducerInterface) {
                $reducer->dispatch($reproducePath);
            }
        }
    }
}

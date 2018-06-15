<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
        /** @var EntityRepository $entityRepository */
        $entityRepository = $this->entityManager->getRepository(ReproducePath::class);
        $reducer = $entityRepository->createQueryBuilder('r')
            ->select('r.reducer')
            ->where('r.id = :reproduce_path_id')
            ->setParameter('reproduce_path_id', $reproducePathMessage->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if ($this->reducerManager->hasPathReducer($reducer)) {
            $pathReducer = $this->reducerManager->getPathReducer($reducer);
            if ($pathReducer instanceof QueuedPathReducerInterface) {
                $pathReducer->dispatch($reproducePathMessage->getId());
            }
        }
    }
}

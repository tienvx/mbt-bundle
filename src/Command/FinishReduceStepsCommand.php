<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceBugMessage;
use Throwable;

class FinishReduceStepsCommand extends AbstractCommand
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:steps:finish-reduce')
            ->setDescription('Finish reduce path')
            ->setHelp('Do things after finish reducing path: reduce messages count, finish reduce bug if needed')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id.')
            ->setHidden(true);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');

        $callback = function () use ($bugId) {
            $bug = $this->entityManager->find(Bug::class, $bugId, LockMode::PESSIMISTIC_WRITE);

            if (!$bug instanceof Bug) {
                throw new Exception(sprintf('No bug found for id %d', $bugId));
            }

            if ($bug->getMessagesCount() > 0) {
                $bug->setMessagesCount($bug->getMessagesCount() - 1);
            }

            return $bug;
        };

        $bug = $this->entityManager->transactional($callback);
        if ($bug instanceof Bug && 0 === $bug->getMessagesCount()) {
            $this->messageBus->dispatch(new FinishReduceBugMessage($bug->getId()));
        }
    }
}

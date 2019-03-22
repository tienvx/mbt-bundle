<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class UpdateBugStatusCommand extends AbstractCommand
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:update-status')
            ->setDescription('Update status of a bug.')
            ->setHelp('This command update status of a bug.')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to update.')
            ->addArgument('status', InputArgument::REQUIRED, 'The status to update.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);

        if (!$bug || !$bug instanceof Bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));
            return;
        }

        $status = $input->getArgument('status');
        $bug->setStatus($status);

        $errors = $this->validator->validate($bug);

        if (count($errors) > 0) {
            $output->writeln((string) $errors);
            return;
        }

        $this->entityManager->flush();
    }
}

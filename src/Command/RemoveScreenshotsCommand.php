<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class RemoveScreenshotsCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var FilesystemInterface
     */
    protected $mbtStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectManager $subjectManager,
        FilesystemInterface $mbtStorage
    ) {
        $this->entityManager = $entityManager;
        $this->subjectManager = $subjectManager;
        $this->mbtStorage = $mbtStorage;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:remove-screenshots')
            ->setDescription('Remove screenshots of a bug.')
            ->setHelp('Remove screenshots of a bug when the bug is removed.')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to report.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model of the task.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');
        $model = $input->getArgument('model');

        $subject = $this->subjectManager->createSubject($model);
        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bugId);
    }
}

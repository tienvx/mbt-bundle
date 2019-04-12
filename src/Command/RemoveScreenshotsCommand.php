<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\Filesystem;
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
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectManager $subjectManager
    ) {
        $this->entityManager = $entityManager;
        $this->subjectManager = $subjectManager;

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

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
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

        if (!$this->filesystem instanceof Filesystem) {
            throw new Exception("Can not remove screenshots: No filesystems with name 'mbt' were defined");
        }

        $subject = $this->subjectManager->createSubject($model);
        $subject->setFilesystem($this->filesystem);
        $subject->removeScreenshots($bugId);
    }
}

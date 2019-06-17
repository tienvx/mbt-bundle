<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class CaptureScreenshotsCommand extends AbstractCommand
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
     * @var Registry
     */
    protected $workflowRegistry;

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
            ->setName('mbt:bug:capture-screenshots')
            ->setDescription('Capture screenshots of a bug.')
            ->setHelp('Capture screenshots of every reproduce steps of a bug.')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to report.');
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
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
        /** @var Bug $bug */
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);

        if (!$bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));

            return;
        }

        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('Can not capture screenshots: No workflows were defined');
        }

        $this->setAnonymousToken();

        $path = $bug->getPath();
        $model = $bug->getTask()->getModel()->getName();
        $subject = $this->subjectManager->createSubject($model);
        $workflow = $this->workflowRegistry->get($subject, $model);

        $subject->setUp();
        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bugId);

        try {
            foreach ($path->getSteps() as $index => $step) {
                $transitionName = $step[0];
                $data = $step[1];
                if ($transitionName) {
                    if (is_array($data)) {
                        $subject->setData($data);
                        $subject->setNeedData(false);
                    } else {
                        $subject->setNeedData(true);
                    }
                    if (!$workflow->can($subject, $transitionName)) {
                        break;
                    }
                    // Store data before apply transition, because there are maybe exception happen
                    // while applying transition.
                    if (!is_array($data)) {
                        $path->setDataAt($index, $subject->getData());
                    }
                    $subject->setNeedData(false);
                    try {
                        $workflow->apply($subject, $transitionName);
                    } catch (Throwable $throwable) {
                    } finally {
                        $subject->captureScreenshot($bugId, $index);
                    }
                }
            }
        } finally {
            $subject->tearDown();
        }
    }
}

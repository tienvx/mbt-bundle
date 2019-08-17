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
use Tienvx\Bundle\MbtBundle\Entity\StepData;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;
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
        Registry $workflowRegistry,
        EntityManagerInterface $entityManager,
        SubjectManager $subjectManager,
        FilesystemInterface $mbtStorage
    ) {
        $this->workflowRegistry = $workflowRegistry;
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

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getTask()->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            return;
        }

        $subject = $this->getSubject($bug->getTask()->getModel()->getName(), $bug->getId());
        $path = $bug->getPath();

        $this->setAnonymousToken();

        try {
            foreach ($path->getSteps() as $index => $step) {
                if ($step->getTransition() && $step->getData() instanceof StepData) {
                    try {
                        $workflow->apply($subject, $step->getTransition(), [
                            'data' => $step->getData(),
                        ]);
                    } catch (Throwable $throwable) {
                    } finally {
                        $subject->captureScreenshot($bugId, $index);
                    }
                } elseif (0 === $index) {
                    $subject->captureScreenshot($bugId, $index);
                }
            }
        } finally {
            $subject->tearDown();
        }
    }

    /**
     * @param string $model
     * @param int    $bugId
     *
     * @return AbstractSubject
     *
     * @throws Exception
     */
    private function getSubject(string $model, int $bugId): AbstractSubject
    {
        $subject = $this->subjectManager->createSubject($model);

        $subject->setUp();
        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bugId);

        return $subject;
    }
}

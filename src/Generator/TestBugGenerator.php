<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Generator;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class TestBugGenerator extends AbstractGenerator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function generate(Workflow $workflow, AbstractSubject $subject, GeneratorOptions $generatorOptions = null): Generator
    {
        $bugId = $generatorOptions->getBugId();

        if (!$bugId) {
            throw new Exception('Missing bug id');
        }
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No task found for id %d', $bugId));
        }

        $task = $bug->getTask();
        if (!$task instanceof Task) {
            throw new Exception(sprintf('Task of bug with id %d is missing', $bugId));
        }

        if ($task->getModel()->getName() !== $workflow->getName()) {
            throw new Exception(sprintf('The bug with id %d can not be tested with workflow %s', $bugId, $workflow->getName()));
        }

        if (!WorkflowHelper::validate($bug->getSteps(), $workflow)) {
            throw new Exception(sprintf('The bug with id %d is outdated with workflow %s', $bugId, $workflow->getName()));
        }

        foreach ($bug->getSteps() as $step) {
            yield $step;
        }
    }

    public static function getName(): string
    {
        return 'test-bug';
    }

    public function getLabel(): string
    {
        return 'Test Bug';
    }
}

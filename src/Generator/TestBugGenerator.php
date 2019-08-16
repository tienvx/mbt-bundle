<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Generator;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
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

        $path = $bug->getPath();

        foreach ($path->getSteps() as $step) {
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

<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Generator;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\StaticCase;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class TestStaticCaseGenerator extends AbstractGenerator
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
        $staticCaseId = $generatorOptions->getStaticCaseId();

        if (!$staticCaseId) {
            throw new Exception('Missing static case id');
        }
        $staticCase = $this->entityManager->getRepository(StaticCase::class)->find($staticCaseId);

        if (!$staticCase || !$staticCase instanceof StaticCase) {
            throw new Exception(sprintf('No static case found for id %d', $staticCaseId));
        }

        $path = $staticCase->getPath();

        foreach ($path->getSteps() as $step) {
            yield $step;
        }
    }

    public static function getName(): string
    {
        return 'test-static-case';
    }

    public function getLabel(): string
    {
        return 'Test Static Case';
    }
}

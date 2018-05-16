<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;
use Tienvx\Bundle\MbtBundle\Service\ReporterManager;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;
use Tienvx\Bundle\MbtBundle\Tests\AbstractTestCase;
use Tienvx\Bundle\MbtBundle\Tests\StopCondition\FoundBugStopCondition;

class ExecuteTaskCommandTest extends AbstractTestCase
{
    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        /** @var ModelRegistry $modelRegistry */
        $modelRegistry = self::$container->get(ModelRegistry::class);
        /** @var GeneratorManager $generatorManager */
        $generatorManager = self::$container->get(GeneratorManager::class);
        /** @var PathReducerManager $pathReducerManager */
        $pathReducerManager = self::$container->get(PathReducerManager::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        /** @var ReporterManager $reporterManager */
        $reporterManager = self::$container->get(ReporterManager::class);
        /** @var StopConditionManager $stopConditionManager */
        $stopConditionManager = self::$container->get(StopConditionManager::class);
        /** @var FoundBugStopCondition $stopCondition */
        $stopCondition = self::$container->get(FoundBugStopCondition::class);

        $this->application->add(new ExecuteTaskCommand($modelRegistry, $generatorManager, $pathReducerManager, $entityManager, $reporterManager, $stopConditionManager));

        $this->runCommand('doctrine:database:drop --force');
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setStopCondition('modified-found-bug');
        $task->setStopConditionArguments('{}');
        $task->setReducer('queued-loop');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);
        $entityManager->flush();

        $command = $this->application->find('mbt:execute-task');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'task-id'      => $task->getId(),
        ]);

        if ($stopCondition->bugFound) {
            /** @var EntityRepository $entityRepository */
            $entityRepository = $entityManager->getRepository(ReproducePath::class);
            $countReproducePaths = $entityRepository->createQueryBuilder('r')
                ->select('count(r.id)')
                ->where('r.task = :task_id')
                ->setParameter('task_id', $task->getId())
                ->getQuery()
                ->getSingleScalarResult();
            $this->assertEquals(1, $countReproducePaths);
        }
        else {
            $this->addToAssertionCount(1);
        }
    }
}

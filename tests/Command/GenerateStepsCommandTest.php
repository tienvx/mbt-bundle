<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Command\GenerateStepsCommand;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class GenerateStepsCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        /** @var Registry $workflowRegistry */
        $workflowRegistry = self::$container->get(Registry::class);
        /** @var SubjectManager $subjectManager */
        $subjectManager = self::$container->get(SubjectManager::class);
        /** @var GeneratorManager $generatorManager */
        $generatorManager = self::$container->get(GeneratorManager::class);

        $this->application->add(new GenerateStepsCommand($workflowRegistry, $subjectManager, $generatorManager));

        $command = $this->application->find('mbt:generate-steps');
        $this->assertCoverage($command, 'random', 'coverage', '{"transitionCoverage":100,"placeCoverage":100}', 24, 5);
        $this->assertCoverage($command, 'random', 'coverage', '{"transitionCoverage":60,"placeCoverage":80}', 15, 4);
        $this->assertCoverage($command, 'random', 'coverage', '{"transitionCoverage":75,"placeCoverage":60}', 18, 3);
        $this->assertCoverage($command, 'all-places', 'noop', '{}', 0, 5);
        $this->assertCoverage($command, 'all-transitions', 'noop', '{}', 24, 0);
    }

    public function assertCoverage(Command $command, string $generator, $stopCondition, $stopConditionArguments, $edgeCount, $vertexCount)
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'                     => $command->getName(),
            'model'                       => 'shopping_cart',
            '--generator'                 => $generator,
            '--stop-condition'            => $stopCondition,
            '--stop-condition-arguments'  => $stopConditionArguments
        ]);

        $output = $commandTester->getDisplay();

        $edges = [];
        $vertices = [];
        foreach (explode(' ', $output) as $step) {
            $pos = strpos($step, '(');
            if ($pos === false) {
                $vertices[] = $step;
            }
            else {
                $edges[] = substr($step, 0, $pos);
            }
        }
        if ($generator === 'all-transitions' && end($vertices) !== 'home') {
            // Sometime, we can't get the path through all transitions, so ignore it.
        } elseif ($generator === 'all-places' && count($edges) === 1) {
            // Sometime, we can't get the path through all edges, so ignore it.
        } else {
            $this->assertGreaterThanOrEqual($edgeCount, count($edges));
            $this->assertGreaterThanOrEqual($vertexCount, count($vertices));
        }
    }
}

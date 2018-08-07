<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\GenerateStepsCommand;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

class GenerateStepsCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        /** @var ModelRegistry $modelRegistry */
        $modelRegistry = self::$container->get(ModelRegistry::class);
        /** @var GeneratorManager $generatorManager */
        $generatorManager = self::$container->get(GeneratorManager::class);
        /** @var StopConditionManager $stopConditionManager */
        $stopConditionManager = self::$container->get(StopConditionManager::class);

        $this->application->add(new GenerateStepsCommand($modelRegistry, $generatorManager, $stopConditionManager));

        $command = $this->application->find('mbt:generate-steps');
        $this->assertCoverage($command, 'random', 'coverage', '{"edgeCoverage":100,"vertexCoverage":100}', 24, 5);
        $this->assertCoverage($command, 'random', 'coverage', '{"edgeCoverage":60,"vertexCoverage":80}', 15, 4);
        $this->assertCoverage($command, 'random', 'coverage', '{"edgeCoverage":75,"vertexCoverage":60}', 18, 3);
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

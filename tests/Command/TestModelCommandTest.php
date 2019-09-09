<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Exception;
use Symfony\Component\Console\Tester\CommandTester;

class TestModelCommandTest extends CommandTestCase
{
    /**
     * @throws Exception
     */
    public function testExecute()
    {
        $name = 'mbt:model:test';
        $input = [
            'command' => $name,
            'model' => 'checkout',
            '--generator' => 'random',
            '--generator-options' => json_encode([
                'maxSteps' => 300,
                'transitionCoverage' => 100,
                'placeCoverage' => 100,
            ]),
        ];

        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Testing model is finished!', $output);
    }
}

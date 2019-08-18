<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Exception;
use Symfony\Component\Console\Tester\CommandTester;

class TestSubjectCommandTest extends CommandTestCase
{
    /**
     * @throws Exception
     */
    public function testExecute()
    {
        $name = 'mbt:subject:test';
        $input = [
            'command' => $name,
            'model' => 'checkout',
            '--generator' => 'random',
            '--generator-options' => [
                'maxSteps' => 300,
                'transitionCoverage' => 100,
                'placeCoverage' => 100,
            ],
        ];

        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();
        $this->assertContains('Testing subject is finished!', $output);
    }
}

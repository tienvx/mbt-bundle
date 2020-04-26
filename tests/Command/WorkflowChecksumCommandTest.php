<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Exception;
use Symfony\Component\Console\Tester\CommandTester;

class WorkflowChecksumCommandTest extends CommandTestCase
{
    /**
     * @dataProvider checksumData
     *
     * @throws Exception
     */
    public function testExecute(string $workflowName, string $checksum)
    {
        $name = 'mbt:workflow:checksum';
        $input = [
            'command' => $name,
            'workflow-name' => $workflowName,
        ];

        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();
        $this->assertEquals("Here is the checksum of workflow $workflowName: $checksum\n", $output);
    }

    public function checksumData()
    {
        $workflows = json_decode(file_get_contents(__DIR__.'/../app/var/checksum.json'), true);

        foreach ($workflows as $workflowName => $checksum) {
            yield [$workflowName, $checksum];
        }
    }
}

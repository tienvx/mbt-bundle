<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class GraphDumpCommandTest extends CommandTestCase
{
    public function modelData()
    {
        return [
            ['article', 'Article', 'dot', 'digraph workflow'],
            ['pull_request', 'Pull Request', 'puml', '@startuml'],
        ];
    }

    /**
     * @dataProvider modelData
     * @param string $model
     * @param string $label
     * @param string $format
     * @param string $contains
     */
    public function testExecute(string $model, string $label, string $format, string $contains)
    {
        $command = $this->application->find('mbt:graph:dump');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'       => $command->getName(),
            'name'          => $model,
            '--label'       => $label,
            '--dump-format' => $format,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains($contains, $output);
    }
}

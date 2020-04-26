<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakeSubjectCommandTest extends CommandTestCase
{
    public function workflowData()
    {
        return [
            ['article', 'Article'],
            ['pull_request', 'PullRequest'],
        ];
    }

    /**
     * @dataProvider workflowData
     */
    public function testExecute(string $workflow, string $subjectClass)
    {
        $command = $this->application->find('make:subject');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'workflow' => $workflow,
            'subject-class' => $subjectClass,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Success!', $output);
        $this->assertStringContainsString('Next: Open the new generated subject class and implement places and transitions!', $output);
        $this->assertStringContainsString("class $subjectClass extends AbstractSubject", file_get_contents(__DIR__."/../app/src/Subject/$subjectClass.php"));
        unlink(__DIR__."/../app/src/Subject/$subjectClass.php");
    }
}

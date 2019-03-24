<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakeSubjectCommandTest extends CommandTestCase
{
    public function modelData()
    {
        return [
            ['article', 'Article'],
            ['pull_request', 'PullRequest'],
        ];
    }

    /**
     * @dataProvider modelData
     *
     * @param string $model
     * @param string $subjectClass
     */
    public function testExecute(string $model, string $subjectClass)
    {
        $command = $this->application->find('make:subject');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'model' => $model,
            'subject-class' => $subjectClass,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Success!', $output);
        $this->assertContains('Next: Open the new generated subject class and implement places and transitions!', $output);
        $this->assertContains("class $subjectClass extends AbstractSubject", file_get_contents(__DIR__."/../app/src/Subject/$subjectClass.php"));
        unlink(__DIR__."/../app/src/Subject/$subjectClass.php");
    }
}

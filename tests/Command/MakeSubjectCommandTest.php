<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Graph\Path;

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
     * @param string $model
     * @param string $subjectClass
     */
    public function testExecute(string $model, string $subjectClass)
    {
        $command = $this->application->find('make:subject');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'       => $command->getName(),
            'model'         => $model,
            'subject-class' => $subjectClass,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Success!', $output);
        $this->assertContains(sprintf('App\Subject\%s', $subjectClass), $output);
        $this->assertContains('Next: Open configuration file, add this line to tienvx_mbt.subjects:', $output);
        unlink(__DIR__ . "/../app/src/Subject/$subjectClass.php");
    }
}

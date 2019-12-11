<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Exception;
use Symfony\Component\Console\Tester\CommandTester;

class ChecksumModelCommandTest extends CommandTestCase
{
    /**
     * @dataProvider checksumData
     *
     * @throws Exception
     */
    public function testExecute(string $model, string $checksum)
    {
        $name = 'mbt:model:checksum';
        $input = [
            'command' => $name,
            'model' => $model,
        ];

        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();
        $this->assertEquals("Here is the checksum of model $model: $checksum\n", $output);
    }

    public function checksumData()
    {
        $models = json_decode(file_get_contents(__DIR__.'/../app/var/checksum.json'), true);

        foreach ($models as $model => $checksum) {
            yield [$model, $checksum];
        }
    }
}

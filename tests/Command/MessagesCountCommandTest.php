<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use App\Message\CountableMessage;
use Exception;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;

class MessagesCountCommandTest extends CommandTestCase
{
    /**
     * @throws Exception
     */
    public function testExecute()
    {
        $name = 'mbt:messages:count';
        $input = [
            'command' => $name,
            'receiver' => 'doctrine',
        ];

        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);

        $commandTester->execute($input);
        $output = $commandTester->getDisplay();
        $this->assertEquals(0, $output);

        $messageBus = self::$container->get(MessageBusInterface::class);
        $messageBus->dispatch(new CountableMessage());

        $commandTester->execute($input);
        $output = $commandTester->getDisplay();
        $this->assertEquals(1, $output);
    }
}

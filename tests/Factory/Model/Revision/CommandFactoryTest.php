<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Factory\Model\Revision;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner;
use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class CommandFactoryTest extends TestCase
{
    protected array $data;

    protected function setUp(): void
    {
        $this->data = [
            'command' => StoreCommandRunner::STORE,
            'target' => '123',
            'value' => 'var',
        ];
    }

    public function testCreateFromArray(): void
    {
        $command = CommandFactory::createFromArray($this->data);
        $this->assertSame('store', $command->getCommand());
        $this->assertSame('123', $command->getTarget());
        $this->assertSame('var', $command->getValue());
    }
}

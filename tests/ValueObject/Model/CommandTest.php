<?php

namespace Tienvx\Bundle\MbtBundle\Tests\ValueObject\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\Model\Revision\CommandTest as CommandModelTest;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Command
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class CommandTest extends CommandModelTest
{
    protected function createCommand(): CommandInterface
    {
        return new Command();
    }
}

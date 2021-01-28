<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory
 */
class TransitionTest extends TestCase
{
    protected TransitionInterface $transition;
    protected CommandInterface $command1;
    protected CommandInterface $command2;

    protected function setUp(): void
    {
        $this->setUpCommands();
        $this->transition = new Transition();
        $this->transition->setGuard('count > 2');
        $this->transition->setFromPlaces([1, 2, 3]);
        $this->transition->setToPlaces([12, 23]);
        $this->transition->setCommands([
            $this->command1,
            $this->command2,
        ]);
    }

    protected function setUpCommands(): void
    {
        $this->command1 = new Command();
        $this->command2 = new Command();
        $this->command1->setCommand(KeyboardCommandRunner::TYPE);
        $this->command1->setTarget('css=.email');
        $this->command1->setValue('test@example.com');
        $this->command2->setCommand(MouseCommandRunner::CLICK);
        $this->command2->setTarget('css=.link');
        $this->command2->setValue(null);
    }

    public function testSerialize(): void
    {
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('O:55:"Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition":5:{s:5:"label";s:0:"";s:5:"guard";s:9:"count > 2";s:10:"fromPlaces";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}s:8:"toPlaces";a:2:{i:0;i:12;i:1;i:23;}s:8:"commands";a:2:{i:0;a:3:{s:7:"command";s:4:"type";s:6:"target";s:10:"css=.email";s:5:"value";s:16:"test@example.com";}i:1;a:3:{s:7:"command";s:5:"click";s:6:"target";s:9:"css=.link";s:5:"value";N;}}}', serialize($this->transition));
    }

    public function testUnerialize(): void
    {
        // phpcs:ignore Generic.Files.LineLength
        $transition = unserialize('O:55:"Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition":5:{s:5:"label";s:10:"Serialized";s:5:"guard";s:10:"count == 3";s:10:"fromPlaces";a:2:{i:0;i:1;i:1;i:4;}s:8:"toPlaces";a:1:{i:0;i:15;}s:8:"commands";a:1:{i:0;a:3:{s:7:"command";s:5:"store";s:6:"target";s:2:"55";s:5:"value";s:6:"number";}}}');
        $this->assertInstanceOf(TransitionInterface::class, $transition);
        $this->assertSame('Serialized', $transition->getLabel());
        $this->assertSame('count == 3', $transition->getGuard());
        $this->assertSame([1, 4], $transition->getFromPlaces());
        $this->assertSame([15], $transition->getToPlaces());
        $this->assertInstanceOf(CommandInterface::class, $transition->getCommands()[0]);
        $this->assertSame(StoreCommandRunner::STORE, $transition->getCommands()[0]->getCommand());
        $this->assertSame('55', $transition->getCommands()[0]->getTarget());
        $this->assertSame('number', $transition->getCommands()[0]->getValue());
    }
}

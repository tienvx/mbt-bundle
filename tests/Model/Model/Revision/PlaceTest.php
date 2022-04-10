<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model\Revision;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @uses \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory
 */
class PlaceTest extends TestCase
{
    protected PlaceInterface $place;
    protected CommandInterface $command1;
    protected CommandInterface $command2;

    protected function setUp(): void
    {
        $this->setUpCommands();
        $this->place = $this->createPlace();
        $this->place->setLabel('place label');
        $this->place->setCommands([
            $this->command1,
            $this->command2,
        ]);
    }

    protected function setUpCommands(): void
    {
        $this->command1 = new Command();
        $this->command2 = new Command();
        $this->command1->setCommand(AssertionRunner::ASSERT_TEXT);
        $this->command1->setTarget('css=.title');
        $this->command1->setValue('Hello');
        $this->command2->setCommand(AssertionRunner::ASSERT_ALERT);
        $this->command2->setTarget('css=.warning');
        $this->command2->setValue('Are you sure?');
    }

    public function testSerialize(): void
    {
        $className = get_class($this->place);
        // phpcs:ignore Generic.Files.LineLength
        $this->assertSame('O:' . strlen($className) . ':"' . $className . '":2:{s:5:"label";s:11:"place label";s:8:"commands";a:2:{i:0;a:3:{s:7:"command";s:10:"assertText";s:6:"target";s:10:"css=.title";s:5:"value";s:5:"Hello";}i:1;a:3:{s:7:"command";s:11:"assertAlert";s:6:"target";s:12:"css=.warning";s:5:"value";s:13:"Are you sure?";}}}', serialize($this->place));
    }

    public function testUnerialize(): void
    {
        $className = get_class($this->place);
        // phpcs:ignore Generic.Files.LineLength
        $place = unserialize('O:' . strlen($className) . ':"' . $className . '":2:{s:5:"label";s:10:"Serialized";s:8:"commands";a:1:{i:0;a:3:{s:7:"command";s:19:"assertSelectedValue";s:6:"target";s:12:"css=.country";s:5:"value";s:2:"vn";}}}');
        $this->assertInstanceOf(PlaceInterface::class, $place);
        $this->assertSame('Serialized', $place->getLabel());
        $this->assertInstanceOf(CommandInterface::class, $place->getCommands()[0]);
        $this->assertSame(AssertionRunner::ASSERT_SELECTED_VALUE, $place->getCommands()[0]->getCommand());
        $this->assertSame('css=.country', $place->getCommands()[0]->getTarget());
        $this->assertSame('vn', $place->getCommands()[0]->getValue());
    }

    protected function createPlace(): PlaceInterface
    {
        return new Place();
    }
}

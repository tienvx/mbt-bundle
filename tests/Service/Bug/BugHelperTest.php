<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Bug;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class BugHelperTest extends TestCase
{
    protected TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
    }

    public function testCreateBug(): void
    {
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('mbt.default_bug_title', ['%id%' => 123])
            ->willReturn('Translated bug title');
        $steps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $helper = new BugHelper($this->translator);
        $bug = $helper->createBug($steps, 'Something wrong', 123);
        $this->assertInstanceOf(BugInterface::class, $bug);
        $this->assertSame($steps, $bug->getSteps());
        $this->assertSame('Translated bug title', $bug->getTitle());
        $this->assertSame('Something wrong', $bug->getMessage());
    }
}

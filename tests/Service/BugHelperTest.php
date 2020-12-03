<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\BugHelper;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\BugHelper
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

    public function testCreate(): void
    {
        $steps = [
            $step1 = $this->createMock(StepInterface::class),
            $step2 = $this->createMock(StepInterface::class),
            $step3 = $this->createMock(StepInterface::class),
        ];
        $message = 'Can not run next step';
        $model = $this->createMock(ModelInterface::class);
        $model->expects($this->once())->method('getLabel')->willReturn('Test shopping cart');
        $model->expects($this->once())->method('getVersion')->willReturn(123);
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('mbt.default_bug_title', ['model' => 'Test shopping cart'])
            ->willReturn('New bug was found during testing model "Test shopping cart"');
        $bugHelper = new BugHelper($this->translator);
        $bug = $bugHelper->create($steps, $message, $model);
        $this->assertSame('New bug was found during testing model "Test shopping cart"', $bug->getTitle());
        $this->assertSame($steps, $bug->getSteps());
        $this->assertSame($message, $bug->getMessage());
        $this->assertSame($model, $bug->getModel());
        $this->assertSame(123, $bug->getModelVersion());
    }

    public function testGetBugUrl(): void
    {
        $bug = new Bug();
        $bug->setId(123);
        $bugHelper = new BugHelper($this->translator);
        $bugHelper->setBugUrl('http://localhost/bug/%s');
        $this->assertSame('http://localhost/bug/123', $bugHelper->buildBugUrl($bug));
    }
}

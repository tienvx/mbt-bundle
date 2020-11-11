<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\BugHelper;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\BugHelper
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class BugHelperTest extends TestCase
{
    protected EntityManagerInterface $entityManager;
    protected ConfigLoaderInterface $configLoader;
    protected TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->configLoader = $this->createMock(ConfigLoaderInterface::class);
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
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->entityManager->expects($this->once())->method('persist')->with($this->callback(function ($bug) use ($steps, $message, $model) {
            return $bug instanceof BugInterface && 'New bug was found during testing model "Test shopping cart"' === $bug->getTitle() && $bug->getSteps()->toArray() === $steps && $bug->getMessage() === $message && $bug->getModel() === $model;
        }));
        $this->translator->expects($this->once())->method('trans')->with('mbt.default_bug_title', ['model' => 'Test shopping cart'])->willReturn('New bug was found during testing model "Test shopping cart"');
        $bugHelper = new BugHelper($this->entityManager, $this->configLoader, $this->translator);
        $bugHelper->create($steps, $message, $model);
    }

    public function testGetBugUrl(): void
    {
        $bug = new Bug();
        $bug->setId(123);
        $bugHelper = new BugHelper($this->entityManager, $this->configLoader, $this->translator);
        $bugHelper->setBugUrl('http://localhost/bug/%s');
        $this->assertSame('http://localhost/bug/123', $bugHelper->buildBugUrl($bug));
    }
}

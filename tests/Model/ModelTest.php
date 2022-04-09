<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class ModelTest extends TestCase
{
    protected ModelInterface $model;
    protected Revision $revision;
    protected ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->revision = new Revision();
        $this->revision->setId(1);
        $this->model = $this->createModel();
        $this->model->setId(1);
        $this->model->setLabel('');
        $this->model->setTags('tag1,tag1,tag2,,tag3');
        $this->model->setAuthor(12);
        $this->model->setActiveRevision($this->revision);
    }

    public function testProperties(): void
    {
        $this->assertSame(1, $this->model->getId());
        $this->assertSame('', $this->model->getLabel());
        $this->assertSame('tag1,tag1,tag2,,tag3', $this->model->getTags());
        $this->assertSame(12, $this->model->getAuthor());
    }

    public function testActiveRevision(): void
    {
        $this->assertSame($this->revision, $this->model->getActiveRevision());
        $revision = new Revision();
        $revision->setId(2);
        $this->model->setActiveRevision($revision);
        $this->assertSame($revision, $this->model->getActiveRevision());
    }

    public function testAddRemoveRevision(): void
    {
        $this->assertTrue($this->model->getRevisions()->contains($this->revision));
        $this->assertCount(1, $this->model->getRevisions());
        $revision = new Revision();
        $this->model->addRevision($revision);
        $this->assertTrue($this->model->getRevisions()->contains($revision));
        $this->assertCount(2, $this->model->getRevisions());
        $this->model->removeRevision($this->revision);
        $this->assertFalse($this->model->getRevisions()->contains($this->revision));
        $this->assertCount(1, $this->model->getRevisions());
        $this->model->removeRevision($revision);
        $this->assertFalse($this->model->getRevisions()->contains($revision));
        $this->assertCount(0, $this->model->getRevisions());
    }

    public function testToArray(): void
    {
        $this->assertSame([
            'label' => '',
            'tags' => 'tag1,tag1,tag2,,tag3',
            'places' => [],
            'transitions' => [],
        ], $this->model->toArray());
    }

    protected function createModel(): ModelInterface
    {
        return new Model();
    }
}

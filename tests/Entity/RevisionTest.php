<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 */
class RevisionTest extends TestCase
{
    protected Revision $revision;

    protected function setUp(): void
    {
        $this->revision = new Revision();
        $this->revision->setId(1);
    }

    public function testModelRevision(): void
    {
        $model = new Model();
        $model->setActiveRevision($this->revision);
        $this->assertSame($this->revision->getId(), $model->getActiveRevision()->getId());
        $this->assertSame($model, $this->revision->getModel());
        $revision = new Revision();
        $revision->setId(2);
        $model->setActiveRevision($revision);
        $this->assertNotSame($this->revision->getId(), $model->getActiveRevision()->getId());
        $this->assertSame($model, $revision->getModel());
    }
}

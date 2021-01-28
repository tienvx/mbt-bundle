<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class RevisionTest extends TestCase
{
    protected Revision $revision;

    protected function setUp(): void
    {
        $this->revision = new Revision();
    }

    public function testGetModel(): void
    {
        $model = new Model();
        $this->revision->setModel($model);
        $this->assertSame($model, $this->revision->getModel());
    }
}

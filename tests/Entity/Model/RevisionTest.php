<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class RevisionTest extends TestCase
{
    protected RevisionInterface $revision;
    protected ModelInterface $model;

    protected function setUp(): void
    {
        $this->revision = new Revision();
        $this->model = new Model();
        $this->model->setLabel('Model label');
    }

    public function testConvertToString(): void
    {
        $this->assertSame('', (string) $this->revision);
        $this->revision->setModel($this->model);
        $this->assertSame($this->model->getLabel(), (string) $this->revision);
    }
}

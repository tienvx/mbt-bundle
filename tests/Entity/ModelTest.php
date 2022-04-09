<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\ModelTest as ModelModelTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class ModelTest extends ModelModelTest
{
    public function testPrePersist(): void
    {
        $this->model->prePersist();
        $this->assertInstanceOf(\DateTime::class, $this->model->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $this->model->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $this->model->prePersist();
        $this->model->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $updatedAt = $this->model->getUpdatedAt());
        $this->model->preUpdate();
        $this->assertTrue(
            $this->model->getUpdatedAt() instanceof \DateTime
            && $updatedAt !== $this->model->getUpdatedAt()
        );
    }

    protected function createModel(): ModelInterface
    {
        return new Model();
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class BugTest extends TestCase
{
    public function testPrePersist(): void
    {
        $bug = new Bug();
        $bug->prePersist();
        $this->assertInstanceOf(\DateTime::class, $bug->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $bug->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $bug = new Bug();
        $bug->prePersist();
        $bug->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $bug->getUpdatedAt());
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity\Petrinet;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Petrinet
 */
class PetrinetTest extends TestCase
{
    public function testPrePersist(): void
    {
        $petrinet = new Petrinet();
        $petrinet->prePersist();
        $this->assertSame(0, $petrinet->getVersion());
    }

    public function testPreUpdate(): void
    {
        $petrinet = new Petrinet();
        $petrinet->prePersist();
        $petrinet->preUpdate();
        $this->assertSame(1, $petrinet->getVersion());
        $petrinet->preUpdate();
        $this->assertSame(2, $petrinet->getVersion());
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Factory\ModelFactory;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Factory\ModelFactory
 *
 * @uses \Tienvx\Bundle\MbtBundle\Factory\Model\RevisionFactory
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class ModelFactoryTest extends TestCase
{
    protected array $data;

    protected function setUp(): void
    {
        $this->data = [
            'label' => 'Custom label',
            'tags' => 'custom,tags',
            'places' => [],
            'transitions' => [],
        ];
    }

    public function testCreateFromArray(): void
    {
        $model = ModelFactory::createFromArray($this->data);
        $this->assertSame('Custom label', $model->getLabel());
        $this->assertSame('custom,tags', $model->getTags());
        $this->assertInstanceOf(RevisionInterface::class, $model->getActiveRevision());
    }
}

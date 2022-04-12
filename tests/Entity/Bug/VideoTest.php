<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity\Bug;

use Tienvx\Bundle\MbtBundle\Entity\Bug\Video;
use Tienvx\Bundle\MbtBundle\Model\Bug\VideoInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\Bug\VideoTest as VideoModelTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug\Video
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Video
 */
class VideoTest extends VideoModelTest
{
    protected function createVideo(): VideoInterface
    {
        return new Video();
    }
}

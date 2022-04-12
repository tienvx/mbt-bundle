<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Bug;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Bug\Video;
use Tienvx\Bundle\MbtBundle\Model\Bug\VideoInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Video
 */
class VideoTest extends TestCase
{
    protected VideoInterface $video;

    protected function setUp(): void
    {
        $this->video = $this->createVideo();
    }

    public function testRecording(): void
    {
        $this->assertFalse($this->video->isRecording());
        $this->video->setRecording(true);
        $this->assertTrue($this->video->isRecording());
    }

    public function testErrorMessage(): void
    {
        $this->assertNull($this->video->getErrorMessage());
        $this->video->setErrorMessage('Something wrong');
        $this->assertSame('Something wrong', $this->video->getErrorMessage());
    }

    protected function createVideo(): VideoInterface
    {
        return new Video();
    }
}

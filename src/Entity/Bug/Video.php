<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Bug;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;
use Tienvx\Bundle\MbtBundle\Model\Bug\Video as VideoModel;

/**
 * @Embeddable
 */
class Video extends VideoModel
{
    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $recording = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $errorMessage = null;
}

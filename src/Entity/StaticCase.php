<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class StaticCase
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull
     */
    private $path;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Path
     *
     * @throws Exception
     */
    public function getPath(): Path
    {
        return Path::deserialize($this->path);
    }

    public function setPath(Path $path)
    {
        $this->path = Path::serialize($path);
    }
}

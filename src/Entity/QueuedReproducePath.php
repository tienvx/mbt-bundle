<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ApiResource
 * @ORM\Entity
 */
class QueuedReproducePath
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     * @MbtAssert\Unique
     */
    private $messageHashes;

    /**
     * @ORM\OneToOne(targetEntity="ReproducePath")
     * @ORM\JoinColumn(name="reproduce_path_id", referencedColumnName="id")
     */
    private $reproducePath;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $indicator;

    public function getId()
    {
        return $this->id;
    }

    public function getMessageHashes(): array
    {
        return $this->messageHashes;
    }

    public function setMessageHashes(array $messageHashes)
    {
        $this->messageHashes = $messageHashes;
    }

    public function getReproducePath(): ReproducePath
    {
        return $this->reproducePath;
    }

    public function setReproducePath(ReproducePath $reproducePath)
    {
        $this->reproducePath = $reproducePath;
    }

    public function getIndicator(): int
    {
        return $this->indicator;
    }

    public function setIndicator(int $indicator)
    {
        $this->indicator = $indicator;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 */
class QueuedLoop
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
     * @ORM\OneToOne(targetEntity="Bug")
     * @ORM\JoinColumn(name="bug_id", referencedColumnName="id")
     */
    private $bug;

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

    public function getBug(): Bug
    {
        return $this->bug;
    }

    public function setBug(Bug $bug)
    {
        $this->bug = $bug;
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

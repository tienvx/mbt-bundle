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
class Bug
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="ReproducePath")
     * @ORM\JoinColumn(name="reproduce_path_id", referencedColumnName="id")
     */
    private $reproducePath;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\Choice({"unverified", "valid", "invalid"})
     */
    private $status;

    public function getId()
    {
        return $this->id;
    }

    public function getReproducePath(): ReproducePath
    {
        return $this->reproducePath;
    }

    public function setReproducePath(ReproducePath $reproducePath)
    {
        $this->reproducePath = $reproducePath;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}

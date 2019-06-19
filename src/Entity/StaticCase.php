<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $model;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull
     */
    private $path;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

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

    /**
     * @MbtAssert\Model
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return new Model($this->model);
    }

    public function setModel(Model $model)
    {
        $this->model = $model->getName();
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     *
     * @throws Exception
     */
    public function prePersist()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(new DateTime());
        }

        if (!$this->getUpdatedAt()) {
            $this->setUpdatedAt(new DateTime());
        }
    }

    /**
     * @ORM\PreUpdate
     *
     * @throws Exception
     */
    public function preUpdate()
    {
        if (!$this->getUpdatedAt()) {
            $this->setUpdatedAt(new DateTime());
        }
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Task
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $model;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $generator;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $metaData;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $reducer;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull
     */
    private $reporters = '[]';

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *     min = 0,
     *     max = 100
     * )
     */
    private $progress = 0;

    /**
     * @ORM\Column(type="string")
     * @Assert\Choice({"not-started", "in-progress", "completed"})
     */
    private $status = 'not-started';

    /**
     * @ORM\OneToMany(targetEntity="Bug", mappedBy="task", cascade={"persist"})
     */
    private $bugs;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $takeScreenshots = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Assert\DateTime
     */
    private $updatedAt;

    public function __construct()
    {
        $this->bugs = new ArrayCollection();
    }

    public function addBug(Bug $bug): void
    {
        $bug->setTask($this);
        $this->bugs->add($bug);
    }

    public function removeBug(Bug $bug): void
    {
        $bug->setTask(null);
        $this->bugs->removeElement($bug);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
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

    /**
     * @MbtAssert\Generator
     *
     * @return Generator
     */
    public function getGenerator(): Generator
    {
        return new Generator($this->generator);
    }

    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator->getName();
    }

    public function setMetaData(array $metaData)
    {
        $this->metaData = json_encode($metaData);
    }

    public function getMetaData(): ?array
    {
        return json_decode($this->metaData, true);
    }

    /**
     * @MbtAssert\Reducer
     *
     * @return Reducer
     */
    public function getReducer(): Reducer
    {
        return new Reducer($this->reducer);
    }

    public function setReducer(Reducer $reducer)
    {
        $this->reducer = $reducer->getName();
    }

    /**
     * @MbtAssert\Reporters
     *
     * @return Reporter[]
     */
    public function getReporters(): array
    {
        $values = [];
        foreach (json_decode($this->reporters, true) as $reporter) {
            $values[] = new Reporter($reporter);
        }

        return $values;
    }

    public function setReporters(array $reporters)
    {
        $values = [];
        foreach ($reporters as $reporter) {
            if ($reporter instanceof Reporter) {
                $values[] = $reporter->getName();
            }
        }
        $this->reporters = json_encode($values);
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress)
    {
        $this->progress = $progress;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return Collection|Bug[]
     */
    public function getBugs()
    {
        return $this->bugs;
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

    public function setTakeScreenshots(bool $takeScreenshots)
    {
        $this->takeScreenshots = $takeScreenshots;
    }

    public function getTakeScreenshots()
    {
        return $this->takeScreenshots;
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

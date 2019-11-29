<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

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
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $model;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $generator;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Type("string")
     */
    private $generatorOptions;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    private $reducer;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    private $reporters = '[]';

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @MbtAssert\TaskStatus
     */
    private $status = TaskWorkflow::NOT_STARTED;

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
     * @var DateTimeInterface
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @MbtAssert\Model
     */
    public function getModel(): Model
    {
        return new Model($this->model);
    }

    public function setModel(Model $model): void
    {
        $this->model = $model->getName();
    }

    /**
     * @MbtAssert\Generator
     */
    public function getGenerator(): Generator
    {
        return new Generator($this->generator);
    }

    public function setGenerator(Generator $generator): void
    {
        $this->generator = $generator->getName();
    }

    public function setGeneratorOptions(?GeneratorOptions $generatorOptions): void
    {
        $this->generatorOptions = $generatorOptions->serialize();
    }

    public function getGeneratorOptions(): GeneratorOptions
    {
        return GeneratorOptions::deserialize($this->generatorOptions);
    }

    /**
     * @MbtAssert\Reducer
     */
    public function getReducer(): Reducer
    {
        return new Reducer($this->reducer);
    }

    public function setReducer(Reducer $reducer): void
    {
        $this->reducer = $reducer->getName();
    }

    /**
     * @MbtAssert\Reporters
     */
    public function getReporters(): array
    {
        $values = [];
        foreach (json_decode($this->reporters, true) as $reporter) {
            $values[] = new Reporter($reporter);
        }

        return $values;
    }

    public function setReporters(array $reporters): void
    {
        $values = [];
        foreach ($reporters as $reporter) {
            if ($reporter instanceof Reporter) {
                $values[] = $reporter->getName();
            }
        }
        $this->reporters = json_encode($values);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getBugs(): ?Collection
    {
        return $this->bugs;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setTakeScreenshots(bool $takeScreenshots): void
    {
        $this->takeScreenshots = $takeScreenshots;
    }

    public function getTakeScreenshots(): bool
    {
        return $this->takeScreenshots;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
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
     */
    public function preUpdate(): void
    {
        if (!$this->getUpdatedAt()) {
            $this->setUpdatedAt(new DateTime());
        }
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class Task implements TaskInterface
{
    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var ReducerInterface
     */
    protected $reducer;

    /**
     * @var Collection
     */
    protected $reporters;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var GeneratorOptions
     */
    protected $generatorOptions;

    /**
     * @var Collection
     */
    protected $bugs;

    /**
     * @var bool
     */
    protected $takeScreenshots;

    /**
     * @var ?DateTimeInterface
     */
    protected $updatedAt;

    /**
     * @var ?DateTimeInterface
     */
    protected $createdAt;

    public function __construct()
    {
        $this->bugs = new ArrayCollection();
        $this->takeScreenshots = false;
        $this->status = TaskWorkflow::NOT_STARTED;
        $this->reporters = '[]';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): TaskInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getWorkflow(): WorkflowInterface
    {
        return $this->workflow;
    }

    public function setWorkflow(WorkflowInterface $workflow): TaskInterface
    {
        $this->workflow = $workflow;

        return $this;
    }

    public function getGenerator(): GeneratorInterface
    {
        return $this->generator;
    }

    public function setGenerator(GeneratorInterface $generator): TaskInterface
    {
        $this->generator = $generator;

        return $this;
    }

    public function setGeneratorOptions(GeneratorOptionsInterface $generatorOptions): TaskInterface
    {
        $this->generatorOptions = $generatorOptions;

        return $this;
    }

    public function getGeneratorOptions(): GeneratorOptionsInterface
    {
        return $this->generatorOptions;
    }

    public function getReducer(): ReducerInterface
    {
        return $this->reducer;
    }

    public function setReducer(ReducerInterface $reducer): TaskInterface
    {
        $this->reducer = $reducer;

        return $this;
    }

    public function getReporters(): array
    {
        $values = [];
        foreach (json_decode($this->reporters, true) as $reporter) {
            $values[] = new Reporter($reporter);
        }

        return $values;
    }

    public function setReporters(array $reporters): TaskInterface
    {
        $values = [];
        foreach ($reporters as $reporter) {
            if ($reporter instanceof ReporterInterface) {
                $values[] = $reporter->getName();
            }
        }
        $this->reporters = json_encode($values);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): TaskInterface
    {
        $this->status = $status;

        return $this;
    }

    public function addBug(Bug $bug): TaskInterface
    {
        $bug->setTask($this);
        $this->bugs->add($bug);

        return $this;
    }

    public function removeBug(Bug $bug): TaskInterface
    {
        $bug->setTask(null);
        $this->bugs->removeElement($bug);

        return $this;
    }

    public function getBugs(): Collection
    {
        return $this->bugs;
    }

    public function setTakeScreenshots(bool $takeScreenshots): TaskInterface
    {
        $this->takeScreenshots = $takeScreenshots;

        return $this;
    }

    public function getTakeScreenshots(): bool
    {
        return $this->takeScreenshots;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): TaskInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): TaskInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}

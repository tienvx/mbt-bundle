<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use SingleColorPetrinet\Model\Color;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Bug as BugModel;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Bug extends BugModel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $title;

    /**
     * @ORM\Column(type="array")
     */
    protected array $steps = [];

    /**
     * @ORM\OneToOne(targetEntity="Task")
     */
    protected TaskInterface $task;

    /**
     * @ORM\Column(type="text")
     */
    protected string $message;

    /**
     * @ORM\Embedded(class="Progress")
     */
    protected ProgressInterface $progress;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $closed = false;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $modelVersion;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected DateTimeInterface $updatedAt;

    public function __construct()
    {
        parent::__construct();
        $this->progress = new Progress();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new DateTime());
    }

    public function setSteps(array $steps): void
    {
        $items = [];
        foreach ($steps as $step) {
            if ($step instanceof StepInterface) {
                $item = [
                    'transition' => $step->getTransition(),
                    'places' => $step->getPlaces(),
                    'color' => $step->getColor()->getValues(),
                ];
                $items[] = $item;
            }
        }

        $this->steps = $items;
    }

    /**
     * @Assert\Valid
     *
     * @return StepInterface[]
     */
    public function getSteps(): array
    {
        $steps = [];
        foreach ($this->steps as $stepData) {
            $steps[] = $this->denormalizeStep($stepData);
        }

        return $steps;
    }

    protected function denormalizeStep(array $stepData): StepInterface
    {
        return new Step(
            $stepData['places'] ?? [],
            new Color($stepData['color'] ?? []),
            $stepData['transition'] ?? -1
        );
    }
}

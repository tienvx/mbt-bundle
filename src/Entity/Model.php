<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tienvx\Bundle\MbtBundle\Model\Model as BaseModel;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Validator\Tags;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @ORM\Entity
 * @ORM\Table(name="model")
 * @ORM\HasLifecycleCallbacks
 */
class Model extends BaseModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $author;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $label = '';

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Tags
     */
    protected ?string $tags = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url
     */
    protected ?string $startUrl = null;

    /**
     * @ORM\Column(type="array")
     */
    protected array $places = [];

    /**
     * @ORM\Column(type="array")
     */
    protected array $transitions = [];

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $version;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
        $this->version = 1;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new DateTime());
    }

    /**
     * @Assert\Valid
     *
     * @return PlaceInterface[]
     */
    public function getPlaces(): array
    {
        $places = [];
        foreach ($this->places as $placeData) {
            $places[] = $this->denormalizePlace($placeData);
        }

        return $places;
    }

    public function setPlaces(array $places): void
    {
        $items = [];
        foreach ($places as $place) {
            if ($place instanceof PlaceInterface) {
                $item = [
                    'label' => $place->getLabel(),
                    'start' => $place->getStart(),
                    'commands' => $this->normalizeCommands($place->getCommands()),
                ];
                $items[] = $item;
            }
        }

        $this->places = $items;
    }

    public function getPlace(int $index): ?PlaceInterface
    {
        return $this->places[$index] ? $this->denormalizePlace($this->places[$index]) : null;
    }

    /**
     * @Assert\Valid
     *
     * @return TransitionInterface[]
     */
    public function getTransitions(): array
    {
        $transitions = [];
        foreach ($this->transitions as $transitionData) {
            $transitions[] = $this->denormalizeTransition($transitionData);
        }

        return $transitions;
    }

    public function setTransitions(array $transitions): void
    {
        $items = [];
        foreach ($transitions as $transition) {
            if ($transition instanceof TransitionInterface) {
                $item = [
                    'label' => $transition->getLabel(),
                    'guard' => $transition->getGuard(),
                    'commands' => $this->normalizeCommands($transition->getCommands()),
                    'fromPlaces' => $transition->getFromPlaces(),
                    'toPlaces' => $transition->getToPlaces(),
                ];
                $items[] = $item;
            }
        }

        $this->transitions = $items;
    }

    public function getTransition(int $index): ?TransitionInterface
    {
        return $this->transitions[$index] ? $this->denormalizeTransition($this->transitions[$index]) : null;
    }

    /**
     * @Assert\Callback
     */
    public function validatePlacesInTransitions(ExecutionContextInterface $context, $payload): void
    {
        $places = array_keys($this->places);
        foreach ($this->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                $fromPlaces = $transition->getFromPlaces();
                if ($fromPlaces && array_diff($fromPlaces, $places)) {
                    $context->buildViolation('From places are invalid')
                        ->atPath(sprintf('transitions[%d].fromPlaces', $index))
                        ->addViolation();
                }
                $toPlaces = $transition->getToPlaces();
                if ($toPlaces && array_diff($toPlaces, $places)) {
                    $context->buildViolation('To places are invalid')
                        ->atPath(sprintf('transitions[%d].toPlaces', $index))
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateStartPlaces(ExecutionContextInterface $context, $payload): void
    {
        $startingPlaces = array_filter($this->places, fn (array $place) => $place['start'] ?? false);
        if (0 === count($startingPlaces)) {
            $context->buildViolation('You must select at least 1 start place')
                ->atPath('places')
                ->addViolation();
        }
    }

    protected function normalizeCommands(array $commands): array
    {
        $items = [];
        foreach ($commands as $command) {
            if ($command instanceof CommandInterface) {
                $items[] = [
                    'command' => $command->getCommand(),
                    'target' => $command->getTarget(),
                    'value' => $command->getValue(),
                ];
            }
        }

        return $items;
    }

    protected function denormalizeCommands(array $commandsData): array
    {
        $commands = [];
        foreach ($commandsData as $commandData) {
            $command = new Command();
            $command->setCommand($commandData['command'] ?? '');
            $command->setTarget($commandData['target'] ?? null);
            $command->setValue($commandData['value'] ?? null);
            $commands[] = $command;
        }

        return $commands;
    }

    protected function denormalizePlace(array $placeData): PlaceInterface
    {
        $place = new Place();
        $place->setLabel($placeData['label'] ?? '');
        $place->setStart($placeData['start'] ?? '');
        $place->setCommands($this->denormalizeCommands($placeData['commands'] ?? []));

        return $place;
    }

    protected function denormalizeTransition(array $transitionData): TransitionInterface
    {
        $transition = new Transition();
        $transition->setLabel($transitionData['label'] ?? '');
        $transition->setGuard($transitionData['guard'] ?? null);
        $transition->setCommands($this->denormalizeCommands($transitionData['commands'] ?? []));
        $transition->setFromPlaces($transitionData['fromPlaces'] ?? []);
        $transition->setToPlaces($transitionData['toPlaces'] ?? []);

        return $transition;
    }
}

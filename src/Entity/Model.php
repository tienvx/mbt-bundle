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
     * @ORM\Column(type="array")
     */
    protected array $startCommands = [];

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
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\Command")
     * })
     * @Assert\Valid
     */
    public function getStartCommands(): array
    {
        return $this->denormalizeCommands($this->startCommands);
    }

    public function setStartCommands(array $startCommands): void
    {
        $this->startCommands = $this->normalizeCommands($startCommands);
    }

    /**
     * @Assert\Valid
     *
     * @return PlaceInterface[]
     */
    public function getPlaces(): array
    {
        return $this->denormalizePlaces($this->places);
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
        return $this->denormalizeTransitions($this->transitions);
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
        foreach ($this->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface) {
                $fromPlaces = $transition->getFromPlaces();
                if ($fromPlaces && array_diff($fromPlaces, array_keys($this->places))) {
                    $context->buildViolation('mbt.model.places_invalid')
                        ->atPath(sprintf('transitions[%d].fromPlaces', $index))
                        ->addViolation();
                }
                $toPlaces = $transition->getToPlaces();
                if ($toPlaces && array_diff($toPlaces, array_keys($this->places))) {
                    $context->buildViolation('mbt.model.places_invalid')
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
            $context->buildViolation('mbt.model.missing_start_places')
                ->atPath('places')
                ->addViolation();
        }
    }

    public function normalize(): array
    {
        return [
            'label' => $this->label,
            'tags' => $this->tags,
            'startCommands' => $this->startCommands,
            'places' => $this->places,
            'transitions' => $this->transitions,
        ];
    }

    public function denormalize(array $data): void
    {
        $this->setLabel($data['label'] ?? '');
        $this->setTags($data['tags'] ?? null);
        $this->setStartCommands($this->denormalizeCommands($data['startCommands'] ?? []));
        $this->setPlaces($this->denormalizePlaces($data['places'] ?? []));
        $this->setTransitions($this->denormalizeTransitions($data['transitions'] ?? []));
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

    protected function denormalizePlaces(array $placesData): array
    {
        return array_map(fn (array $placeData) => $this->denormalizePlace($placeData), $placesData);
    }

    protected function denormalizePlace(array $placeData): PlaceInterface
    {
        $place = new Place();
        $place->setLabel($placeData['label'] ?? '');
        $place->setStart($placeData['start'] ?? '');
        $place->setCommands($this->denormalizeCommands($placeData['commands'] ?? []));

        return $place;
    }

    protected function denormalizeTransitions(array $transitionsData): array
    {
        return array_map(fn (array $transitionData) => $this->denormalizeTransition($transitionData), $transitionsData);
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

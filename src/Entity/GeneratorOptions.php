<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class GeneratorOptions
{
    /**
     * @var int
     * @Assert\Range(
     *     min = 0,
     *     max = 100
     * )
     */
    private $transitionCoverage;

    /**
     * @var int|null
     * @Assert\Range(
     *     min = 0,
     *     max = 100
     * )
     */
    private $placeCoverage;

    /**
     * @var int|null
     * @Assert\Positive
     */
    private $maxSteps;

    public function getMaxSteps(): ?int
    {
        return $this->maxSteps;
    }

    public function setMaxSteps(?int $maxSteps): void
    {
        $this->maxSteps = $maxSteps;
    }

    public function getPlaceCoverage(): ?int
    {
        return $this->placeCoverage;
    }

    public function setPlaceCoverage(?int $placeCoverage): void
    {
        $this->placeCoverage = $placeCoverage;
    }

    public function getTransitionCoverage(): ?int
    {
        return $this->transitionCoverage;
    }

    public function setTransitionCoverage(?int $transitionCoverage): void
    {
        $this->transitionCoverage = $transitionCoverage;
    }

    public function normalize(): array
    {
        $values = [
            'transitionCoverage' => $this->getTransitionCoverage(),
            'placeCoverage' => $this->getPlaceCoverage(),
            'maxSteps' => $this->getMaxSteps(),
        ];

        return array_filter($values);
    }

    public function serialize(): string
    {
        return json_encode($this->normalize());
    }

    public static function denormalize(?array $data): GeneratorOptions
    {
        if (!$data) {
            return new GeneratorOptions();
        }
        $generatorOptions = new GeneratorOptions();
        $generatorOptions->setTransitionCoverage($data['transitionCoverage'] ?? null);
        $generatorOptions->setPlaceCoverage($data['placeCoverage'] ?? null);
        $generatorOptions->setMaxSteps($data['maxSteps'] ?? null);

        return $generatorOptions;
    }

    public static function deserialize(?string $data): GeneratorOptions
    {
        if (!$data) {
            return new GeneratorOptions();
        }

        return self::denormalize(json_decode($data, true));
    }
}

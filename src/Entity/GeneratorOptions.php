<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

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
    private $maxPathLength;

    /**
     * @var int|null
     * @MbtAssert\BugId
     */
    private $bugId;

    /**
     * @return int|null
     */
    public function getBugId(): ?int
    {
        return $this->bugId;
    }

    /**
     * @param int|null $bugId
     */
    public function setBugId(?int $bugId): void
    {
        $this->bugId = $bugId;
    }

    /**
     * @return int|null
     */
    public function getMaxPathLength(): ?int
    {
        return $this->maxPathLength;
    }

    /**
     * @param int|null $maxPathLength
     */
    public function setMaxPathLength(?int $maxPathLength): void
    {
        $this->maxPathLength = $maxPathLength;
    }

    /**
     * @return int|null
     */
    public function getPlaceCoverage(): ?int
    {
        return $this->placeCoverage;
    }

    /**
     * @param int|null $placeCoverage
     */
    public function setPlaceCoverage(?int $placeCoverage): void
    {
        $this->placeCoverage = $placeCoverage;
    }

    /**
     * @return int|null
     */
    public function getTransitionCoverage(): ?int
    {
        return $this->transitionCoverage;
    }

    /**
     * @param int|null $transitionCoverage
     */
    public function setTransitionCoverage(?int $transitionCoverage): void
    {
        $this->transitionCoverage = $transitionCoverage;
    }

    /**
     * @param GeneratorOptions $generatorOptions
     *
     * @return array
     */
    public static function normalize(GeneratorOptions $generatorOptions): array
    {
        $values = [
            'transitionCoverage' => $generatorOptions->getTransitionCoverage(),
            'placeCoverage' => $generatorOptions->getPlaceCoverage(),
            'maxPathLength' => $generatorOptions->getMaxPathLength(),
            'bugId' => $generatorOptions->getBugId(),
        ];

        return $values;
    }

    /**
     * @param GeneratorOptions $generatorOptions
     *
     * @return string
     */
    public static function serialize(GeneratorOptions $generatorOptions): string
    {
        return json_encode(self::normalize($generatorOptions));
    }

    /**
     * @param array $data
     *
     * @return GeneratorOptions
     */
    public static function denormalize(?array $data): GeneratorOptions
    {
        if (!$data) {
            return new GeneratorOptions();
        }
        $generatorOptions = new GeneratorOptions();
        $generatorOptions->setTransitionCoverage($data['transitionCoverage'] ?? null);
        $generatorOptions->setPlaceCoverage($data['placeCoverage'] ?? null);
        $generatorOptions->setMaxPathLength($data['maxPathLength'] ?? null);
        $generatorOptions->setBugId($data['bugId'] ?? null);

        return $generatorOptions;
    }

    /**
     * @param string $data
     *
     * @return GeneratorOptions
     */
    public static function deserialize(?string $data): GeneratorOptions
    {
        if (!$data) {
            return new GeneratorOptions();
        }

        return self::denormalize(json_decode($data, true));
    }
}

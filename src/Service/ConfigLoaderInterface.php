<?php

namespace Tienvx\Bundle\MbtBundle\Service;

interface ConfigLoaderInterface
{
    public const GENERATOR = 'generator';
    public const MAX_STEPS = 'max_steps';
    public const MAX_TRANSITION_COVERAGE = 'max_transition_coverage';
    public const MAX_PLACE_COVERAGE = 'max_place_coverage';
    public const REDUCER = 'reducer';
    public const NOTIFY_CHANNELS = 'notify_channels';

    public function getGenerator(): string;

    public function getMaxSteps(): int;

    public function getMaxTransitionCoverage(): float;

    public function getMaxPlaceCoverage(): float;

    public function getReducer(): string;

    public function getNotifyChannels(): array;
}

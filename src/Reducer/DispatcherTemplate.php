<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

abstract class DispatcherTemplate implements DispatcherInterface
{
    protected const MIN_PAIR_LENGTH = 2; // 3 steps

    protected MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(BugInterface $bug): int
    {
        $steps = $bug->getSteps();

        if (count($steps) < $this->minSteps()) {
            return 0;
        }

        $pairs = $this->getPairs($steps);

        foreach ($pairs as $pair) {
            $message = new ReduceStepsMessage($bug->getId(), count($steps), $pair[0], $pair[1]);
            $this->messageBus->dispatch($message);
        }

        return count($pairs);
    }

    abstract protected function getPairs(array $steps): array;

    protected function maxPairs(array $steps): int
    {
        return ceil(sqrt(count($steps)));
    }

    abstract protected function minSteps(): int;
}

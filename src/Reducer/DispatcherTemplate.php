<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

abstract class DispatcherTemplate implements DispatcherInterface
{
    protected MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(BugInterface $bug): int
    {
        $steps = $bug->getSteps();

        if ($steps->count() <= 2) {
            return 0;
        }

        $pairs = $this->getPairs($steps);

        foreach ($pairs as $pair) {
            $message = new ReduceStepsMessage($bug->getId(), $steps->count(), $pair[0], $pair[1]);
            $this->messageBus->dispatch($message);
        }

        return count($pairs);
    }

    protected function getPairs(Collection $steps): array
    {
        return [];
    }

    protected function maxPairs(Collection $steps): int
    {
        return floor(sqrt($steps->count()));
    }
}

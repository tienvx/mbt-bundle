<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepsInterface;
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

        if ($steps->getLength() <= 2) {
            return 0;
        }

        $pairs = $this->getPairs($steps);

        foreach ($pairs as $pair) {
            $message = new ReduceStepsMessage($bug->getId(), $steps->getLength(), $pair[0], $pair[1]);
            $this->messageBus->dispatch($message);
        }

        return count($pairs);
    }

    protected function getPairs(StepsInterface $steps): array
    {
        return [];
    }

    protected function maxPairs(StepsInterface $steps): int
    {
        return floor(sqrt($steps->getLength()));
    }
}

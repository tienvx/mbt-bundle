<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

abstract class DispatcherTemplate implements DispatcherInterface
{
    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(Bug $bug): int
    {
        $steps = $bug->getSteps();

        if ($steps->getLength() <= 2) {
            return 0;
        }

        $pairs = $this->getPairs($steps);

        foreach ($pairs as $pair) {
            $message = new ReduceStepsMessage($bug->getId(), static::getReducerName(), $steps->getLength(), $pair[0], $pair[1]);
            $this->messageBus->dispatch($message);
        }

        return count($pairs);
    }

    protected function getPairs(Steps $steps): array
    {
        return [];
    }
}

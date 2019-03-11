<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Exception;
use Psr\SimpleCache\CacheException;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;

class BinaryPathReducer extends AbstractPathReducer
{
    /**
     * @param ReductionMessage $message
     * @throws Exception
     * @throws CacheException
     */
    public function handle(ReductionMessage $message)
    {
        $bug = $this->entityManager->find(Bug::class, $message->getBugId());

        if (!$bug || !$bug instanceof Bug) {
            return;
        }

        $model = $bug->getTask()->getModel();
        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);

        $graph = $this->graphBuilder->build($workflow);
        $path = Path::unserialize($bug->getPath());

        $messagesCount = 0;
        if ($bug->getLength() >= $message->getData()['length']) {
            // The reproduce path has not been reduced.
            list($i, $j) = $message->getData()['pair'];
            $newPath = PathBuilder::createWithShortestPath($graph, $path, $i, $j);
            // Make sure new path shorter than old path.
            if ($newPath->countPlaces() < $path->countPlaces()) {
                try {
                    $subject = $this->subjectManager->createSubject($model);
                    PathRunner::run($newPath, $workflow, $subject);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                        $messagesCount = $this->dispatch($bug->getId(), $newPath);
                    }
                }
            }
        }

        if ($messagesCount === 0) {
            $this->postHandle($message);
        }
    }

    /**
     * @param int $bugId
     * @param Path|null $newPath
     * @param ReductionMessage|null $message
     * @return int
     * @throws Exception
     */
    public function dispatch(int $bugId, Path $newPath = null, ReductionMessage $message = null): int
    {
        $this->entityManager->beginTransaction();
        try {
            $bug = $this->entityManager->find(Bug::class, $bugId, LockMode::PESSIMISTIC_WRITE);

            if (!$bug || !$bug instanceof Bug) {
                return 0;
            }

            if ($newPath) {
                $bug->setPath(Path::serialize($newPath));
                $bug->setLength($newPath->countPlaces());
                $path = $newPath;
            } else {
                $path = Path::unserialize($bug->getPath());
            }

            $messagesCount = 0;
            $divisor = $message ? ($message->getData()['divisor'] * 2) : 2;
            $quotient = floor($path->countTransitions() / $divisor);
            $remainder = $path->countTransitions() % $divisor;

            if ($quotient > 0 && $path->countTransitions() >= 0) {
                for ($k = 0; $k < $divisor; $k++) {
                    $i = $quotient * $k;
                    if ($k === ($divisor - 1)) {
                        $j = $quotient * ($k + 1) + $remainder;
                    } else {
                        $j = $quotient * ($k + 1);
                    }
                    $message = new ReductionMessage($bug->getId(), static::getName(), [
                        'length' => $path->countPlaces(),
                        'pair' => [$i, $j],
                        'divisor' => $divisor,
                    ]);
                    $this->messageBus->dispatch($message);
                    $messagesCount++;
                }
            }

            $bug->setMessagesCount($messagesCount);

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $messagesCount;
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->rollBack();
            return 0;
        }
    }

    public static function getName()
    {
        return 'binary';
    }
}

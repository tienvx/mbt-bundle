<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Exception;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;

class LoopPathReducer extends AbstractPathReducer
{
    /**
     * @param ReductionMessage $message
     * @throws Exception
     */
    public function handle(ReductionMessage $message)
    {
        $bug = $this->entityManager->find(Bug::class, $message->getBugId());

        if (!$bug || !$bug instanceof Bug) {
            return;
        }

        $path = Path::unserialize($bug->getPath());
        $model = $bug->getTask()->getModel();

        $messagesCount = 0;
        if ($bug->getLength() >= $message->getData()['length']) {
            // The reproduce path has not been reduced.
            list($i, $j) = $message->getData()['pair'];
            if ($j <= $path->countPlaces() && !array_diff($path->getPlacesAt($i), $path->getPlacesAt($j))) {
                $newPath = PathBuilder::createWithoutLoop($path, $i, $j);
                // Make sure new path shorter than old path.
                if ($newPath->countPlaces() < $path->countPlaces()) {
                    try {
                        $subject = $this->subjectManager->createSubject($model);
                        $workflow = $this->workflowRegistry->get($subject, $model);
                        PathRunner::run($newPath, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                            $messagesCount = $this->dispatch($bug->getId(), $newPath);
                        }
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
            if ($message && $message->getData()['distance'] <= 1) {
                return 0;
            }

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
            $distance = $newPath ? $newPath->countPlaces() :
                ($message ? ($message->getData()['distance'] - 1) : $bug->getLength());
            while ($distance > 0 && $messagesCount === 0) {
                for ($i = 0; $i < $path->countPlaces(); $i++) {
                    $j = $i + $distance;
                    if ($j < $path->countPlaces() && !array_diff($path->getPlacesAt($i), $path->getPlacesAt($j))) {
                        $pair = [$i, $j];
                        $message = new ReductionMessage($bug->getId(), static::getName(), [
                            'length' => $path->countPlaces(),
                            'pair' => $pair,
                            'distance' => $distance,
                        ]);
                        $this->messageBus->dispatch($message);
                        $messagesCount++;
                    }
                }
                $distance--;
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
        return 'loop';
    }
}

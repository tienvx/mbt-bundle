<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Exception;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;
use Tienvx\Bundle\MbtBundle\Message\ReducePathMessage;

class TransitionPathReducer extends AbstractPathReducer
{
    /**
     * @param int $bugId
     * @param int $length
     * @param int $from
     * @param int $to
     *
     * @throws Exception
     */
    public function handle(int $bugId, int $length, int $from, int $to)
    {
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug || !$bug instanceof Bug) {
            return;
        }

        $path = Path::unserialize($bug->getPath());
        $model = $bug->getTask()->getModel();

        if ($bug->getLength() >= $length) {
            // The reproduce path has not been reduced.
            $fromPlaces = $path->getPlacesAt($from);
            $toPlaces = $path->getPlacesAt($to);
            if (count($fromPlaces) > 1 && count($toPlaces) > 1 && 1 === count(array_diff($fromPlaces, $toPlaces)) &&
                1 === count(array_diff($toPlaces, $fromPlaces))) {
                $newPath = PathBuilder::createWithoutTransition($path, $from, $to);
                // Make sure new path shorter than old path.
                if ($newPath->countPlaces() < $path->countPlaces()) {
                    try {
                        $subject = $this->subjectManager->createSubject($model);
                        $workflow = $this->workflowRegistry->get($subject, $model);
                        PathRunner::run($newPath, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                            $this->dispatch($bug->getId(), $newPath);
                        }
                    }
                }
            }
        }

        $this->postHandle($bugId);
    }

    /**
     * @param int       $bugId
     * @param Path|null $newPath
     *
     * @return int
     *
     * @throws Exception
     */
    public function dispatch(int $bugId, Path $newPath = null): int
    {
        $callback = function () use ($bugId, $newPath) {
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
            for ($i = 0; $i < $path->countPlaces() - 1; ++$i) {
                $fromPlaces = $path->getPlacesAt($i);
                $toPlaces = $path->getPlacesAt($i + 1);
                if (count($fromPlaces) > 1 && count($toPlaces) > 1 && 1 === count(array_diff($fromPlaces, $toPlaces)) &&
                    1 === count(array_diff($toPlaces, $fromPlaces))) {
                    $message = new ReducePathMessage($bug->getId(), static::getName(), $path->countPlaces(), $i, $i + 1);
                    $this->messageBus->dispatch($message);
                    ++$messagesCount;
                    if ($messagesCount >= $path->countPlaces()) {
                        break;
                    }
                }
            }

            $bug->setMessagesCount($bug->getMessagesCount() + $messagesCount);

            return $messagesCount;
        };

        $messagesCount = $this->entityManager->transactional($callback);

        return true === $messagesCount ? 0 : $messagesCount;
    }

    public static function getName()
    {
        return 'transition';
    }
}
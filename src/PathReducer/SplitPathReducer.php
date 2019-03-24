<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Message\ReducePathMessage;

class SplitPathReducer extends AbstractPathReducer
{
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
            if ($path->countPlaces() > 2) {
                $divisor = 2;
                $quotient = floor($path->countPlaces() / $divisor);
                $remainder = $path->countPlaces() % $divisor;
                while ($quotient > 1) {
                    for ($k = 0; $k < $divisor; ++$k) {
                        $i = $quotient * $k;
                        if ($k === ($divisor - 1)) {
                            $j = $quotient * ($k + 1) - 1 + $remainder;
                        } else {
                            $j = $quotient * ($k + 1) - 1;
                        }
                        $message = new ReducePathMessage($bug->getId(), static::getName(), $path->countPlaces(), $i, $j);
                        $this->messageBus->dispatch($message);
                        ++$messagesCount;
                        if ($messagesCount >= floor(sqrt($path->countPlaces()))) {
                            break 2;
                        }
                    }

                    ++$divisor;
                    $quotient = floor($path->countPlaces() / $divisor);
                    $remainder = $path->countPlaces() % $divisor;
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
        return 'split';
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class StepsRunner implements StepsRunnerInterface
{
    protected const WAIT_SECONDS = 2;

    protected SelenoidHelperInterface $selenoidHelper;
    protected StepRunnerInterface $stepRunner;

    public function __construct(SelenoidHelperInterface $selenoidHelper, StepRunnerInterface $stepRunner)
    {
        $this->selenoidHelper = $selenoidHelper;
        $this->stepRunner = $stepRunner;
    }

    /**
     * @throws ExceptionInterface
     */
    public function run(
        iterable $steps,
        TaskInterface|BugInterface $entity,
        bool $debug = false,
        ?callable $exceptionCallback = null,
        ?callable $runCallback = null
    ): void {
        try {
            $driver = $this->selenoidHelper->createDriver($this->selenoidHelper->getCapabilities($entity, $debug));
            if ($debug) {
                $this->waitForVideoContainer();
            }
            foreach ($steps as $step) {
                if (!$step instanceof StepInterface) {
                    throw new UnexpectedValueException(sprintf('Step must be instance of "%s".', StepInterface::class));
                }
                $this->stepRunner->run(
                    $step,
                    ($entity instanceof BugInterface ? $entity->getTask() : $entity)->getModelRevision(),
                    $driver
                );
                if (is_callable($runCallback)) {
                    if ($runCallback($step)) {
                        break;
                    }
                }
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            if (is_callable($exceptionCallback)) {
                $exceptionCallback($throwable, $step ?? null);
            }
        } finally {
            if (isset($driver)) {
                $driver->quit();
            }
        }
    }

    protected function waitForVideoContainer(): void
    {
        sleep(static::WAIT_SECONDS);
    }
}

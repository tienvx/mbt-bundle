<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;

abstract class StepsRunner implements StepsRunnerInterface
{
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
    public function run(iterable $steps, DebugInterface $entity, callable $handleException): void
    {
        try {
            $driver = $this->start($entity);
            foreach ($steps as $step) {
                if (!$step instanceof StepInterface) {
                    throw new UnexpectedValueException(sprintf('Step must be instance of "%s".', StepInterface::class));
                }
                $this->runStep($step, $entity->getTask()->getModelRevision(), $driver);
                if ($this->canStop($step)) {
                    break;
                }
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            $this->catchException($handleException, $throwable, $step ?? null);
        } finally {
            $this->stop($driver ?? null);
        }
    }

    protected function start(DebugInterface $entity): RemoteWebDriver
    {
        return $this->selenoidHelper->createDriver($entity);
    }

    protected function runStep(StepInterface $step, RevisionInterface $revision, RemoteWebDriver $driver): void
    {
        $this->stepRunner->run($step, $revision, $driver);
    }

    protected function canStop(StepInterface $step): bool
    {
        return false;
    }

    abstract protected function catchException(
        callable $handleException,
        Throwable $throwable,
        ?StepInterface $step
    ): void;

    protected function stop(?RemoteWebDriver $driver): void
    {
        $driver?->quit();
    }
}

<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Petrinet\Builder\MarkingBuilder;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Service\GuardedTransitionService;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManager;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManagerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessor;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManagerInterface;
use Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\MessageHandler\RecordVideoMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\RunTaskMessageHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomDispatcher;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomReducer;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitDispatcher;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitReducer;
use Tienvx\Bundle\MbtBundle\Repository\BugRepository;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepository;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogic;
use Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogicInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugNotifierInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelDumper;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelDumperInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelper;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Builder\ShortestPathStepsBuilder;
use Tienvx\Bundle\MbtBundle\Service\Step\Builder\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\ExploreStepsRunner;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\RecordStepsRunner;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\ReduceStepsRunner;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\StepRunner;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\StepHelper;
use Tienvx\Bundle\MbtBundle\Service\Step\StepHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelper;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;
use Tienvx\Bundle\MbtBundle\Validator\TagsValidator;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(ChannelManager::class)
            ->alias(ChannelManagerInterface::class, ChannelManager::class)

        ->set(EntitySubscriber::class)
            ->tag('doctrine.event_subscriber')
            ->args([
                service(MessageBusInterface::class),
            ])

        ->set(GeneratorManager::class)
            ->alias(GeneratorManagerInterface::class, GeneratorManager::class)

        ->set(RandomGenerator::class)
            ->args([
                service(PetrinetHelperInterface::class),
                service(MarkingHelperInterface::class),
                service(ModelHelperInterface::class),
                service(GuardedTransitionServiceInterface::class),
            ])
            ->autoconfigure(true)

        // Message handlers
        ->set(RunTaskMessageHandler::class)
            ->args([
                service(TaskHelperInterface::class),
            ])
            ->autoconfigure(true)
        ->set(RecordVideoMessageHandler::class)
            ->args([
                service(BugHelperInterface::class),
            ])
            ->autoconfigure(true)
        ->set(ReduceBugMessageHandler::class)
            ->args([
                service(BugHelperInterface::class),
            ])
            ->autoconfigure(true)
        ->set(ReduceStepsMessageHandler::class)
            ->args([
                service(BugHelperInterface::class),
            ])
            ->autoconfigure(true)
        ->set(ReportBugMessageHandler::class)
            ->args([
                service(BugHelperInterface::class),
            ])
            ->autoconfigure(true)
        ->set(ReducerManager::class)
            ->alias(ReducerManagerInterface::class, ReducerManager::class)

        // Reducers
        ->set(RandomDispatcher::class)
            ->args([
                service(MessageBusInterface::class),
            ])
        ->set(RandomHandler::class)
            ->args([
                service(BugRepositoryInterface::class),
                service(MessageBusInterface::class),
                service(ReduceStepsRunner::class),
                service(StepsBuilderInterface::class),
                service(StepHelperInterface::class),
            ])
        ->set(RandomReducer::class)
            ->args([
                service(RandomDispatcher::class),
                service(RandomHandler::class),
            ])
            ->autoconfigure(true)
        ->set(SplitDispatcher::class)
            ->args([
                service(MessageBusInterface::class),
            ])
        ->set(SplitHandler::class)
            ->args([
                service(BugRepositoryInterface::class),
                service(MessageBusInterface::class),
                service(ReduceStepsRunner::class),
                service(StepsBuilderInterface::class),
                service(StepHelperInterface::class),
            ])
        ->set(SplitReducer::class)
            ->args([
                service(SplitDispatcher::class),
                service(SplitHandler::class),
            ])
            ->autoconfigure(true)

        // Validators
        ->set(TagsValidator::class)
        ->set(ValidCommandValidator::class)
            ->args([
                service(CommandRunnerManagerInterface::class),
            ])
            ->tag('validator.constraint_validator', [
                'alias' => ValidCommandValidator::class,
            ])

        // Commands
        ->set(CommandPreprocessor::class)
            ->alias(CommandPreprocessorInterface::class, CommandPreprocessor::class)
        ->set(CommandRunnerManager::class)
            ->args([
                tagged_iterator(CommandRunnerInterface::TAG),
                service(CommandPreprocessorInterface::class),
            ])
            ->alias(CommandRunnerManagerInterface::class, CommandRunnerManager::class)

        ->set(AlertCommandRunner::class)
            ->autoconfigure(true)
        ->set(AssertionRunner::class)
            ->autoconfigure(true)
        ->set(KeyboardCommandRunner::class)
            ->autoconfigure(true)
        ->set(MouseCommandRunner::class)
            ->autoconfigure(true)
        ->set(ScriptCommandRunner::class)
            ->autoconfigure(true)
        ->set(StoreCommandRunner::class)
            ->autoconfigure(true)
        ->set(WaitCommandRunner::class)
            ->autoconfigure(true)
        ->set(WindowCommandRunner::class)
            ->autoconfigure(true)

        // Repositories
        ->set(BugRepository::class)
            ->args([
                service(ManagerRegistry::class),
            ])
            ->tag('doctrine.repository_service')
            ->alias(BugRepositoryInterface::class, BugRepository::class)
        ->set(TaskRepository::class)
            ->args([
                service(ManagerRegistry::class),
            ])
            ->tag('doctrine.repository_service')
            ->alias(TaskRepositoryInterface::class, TaskRepository::class)

        // Services
        ->set(ExpressionLanguage::class)

        ->set(SelenoidHelper::class)
            ->alias(SelenoidHelperInterface::class, SelenoidHelper::class)

        ->set(ModelDumper::class)
            ->alias(ModelDumperInterface::class, ModelDumper::class)

        ->set(ModelHelper::class)
            ->alias(ModelHelperInterface::class, ModelHelper::class)

        ->set(BugHelper::class)
            ->args([
                service(ReducerManagerInterface::class),
                service(BugRepositoryInterface::class),
                service(MessageBusInterface::class),
                service(BugNotifierInterface::class),
                service(StepHelperInterface::class),
                service(RecordStepsRunner::class),
                service(ConfigInterface::class),
            ])
            ->alias(BugHelperInterface::class, BugHelper::class)

        ->set(TaskHelper::class)
            ->args([
                service(GeneratorManagerInterface::class),
                service(TaskRepositoryInterface::class),
                service(ExploreStepsRunner::class),
                service(ConfigInterface::class),
            ])
            ->alias(TaskHelperInterface::class, TaskHelper::class)

        ->set(ShortestPathStepsBuilder::class)
            ->args([
                service(PetrinetHelperInterface::class),
                service(PetrinetDomainLogicInterface::class),
            ])
            ->alias(StepsBuilderInterface::class, ShortestPathStepsBuilder::class)

        ->set(PetrinetDomainLogic::class)
            ->args([
                service(GuardedTransitionServiceInterface::class),
                service(MarkingHelperInterface::class),
            ])
            ->alias(PetrinetDomainLogicInterface::class, PetrinetDomainLogic::class)

        ->set(StepHelper::class)
            ->args([
                service(ModelHelperInterface::class),
            ])
            ->alias(StepHelperInterface::class, StepHelper::class)

        ->set(StepRunner::class)
            ->args([
                service(CommandRunnerManagerInterface::class),
            ])
            ->alias(StepRunnerInterface::class, StepRunner::class)

        ->set(ExploreStepsRunner::class)
            ->args([
                service(SelenoidHelperInterface::class),
                service(StepRunnerInterface::class),
                service(ConfigInterface::class),
            ])
        ->set(ReduceStepsRunner::class)
            ->args([
                service(SelenoidHelperInterface::class),
                service(StepRunnerInterface::class),
                service(PetrinetHelperInterface::class),
                service(MarkingHelperInterface::class),
                service(GuardedTransitionServiceInterface::class),
            ])
        ->set(RecordStepsRunner::class)
            ->args([
                service(SelenoidHelperInterface::class),
                service(StepRunnerInterface::class),
            ])

        ->set(MarkingHelper::class)
            ->args([
                service(ColorfulFactoryInterface::class),
            ])
            ->alias(MarkingHelperInterface::class, MarkingHelper::class)

        ->set(PetrinetHelper::class)
            ->args([
                service(ColorfulFactoryInterface::class),
                service(ExpressionLanguage::class),
            ])
            ->alias(PetrinetHelperInterface::class, PetrinetHelper::class)

        // Single Color Petrinet services
        ->set(ColorfulFactory::class)
            ->alias(ColorfulFactoryInterface::class, ColorfulFactory::class)

        ->set(GuardedTransitionService::class)
            ->args([
                service(ColorfulFactoryInterface::class),
            ])
            ->alias(GuardedTransitionServiceInterface::class, GuardedTransitionService::class)

        ->set(MarkingBuilder::class)
            ->args([
                service(ColorfulFactoryInterface::class),
            ])
    ;
};

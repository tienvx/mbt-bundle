<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\EntityManagerInterface;
use Petrinet\Builder\MarkingBuilder;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Service\ExpressionEvaluatorInterface;
use SingleColorPetrinet\Service\ExpressionLanguageEvaluator;
use SingleColorPetrinet\Service\GuardedTransitionService;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManager;
use Tienvx\Bundle\MbtBundle\Channel\EmailChannel;
use Tienvx\Bundle\MbtBundle\Channel\NexmoChannel;
use Tienvx\Bundle\MbtBundle\Channel\SlackChannel;
use Tienvx\Bundle\MbtBundle\Channel\TelegramChannel;
use Tienvx\Bundle\MbtBundle\Channel\TwilioChannel;
use Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\MessageHandler\ExecuteTaskMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomDispatcher;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomReducer;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitDispatcher;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitReducer;
use Tienvx\Bundle\MbtBundle\Service\AStarStrategy;
use Tienvx\Bundle\MbtBundle\Service\BugHelper;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\BugProgress;
use Tienvx\Bundle\MbtBundle\Service\BugProgressInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Service\Generator\StateHelper;
use Tienvx\Bundle\MbtBundle\Service\Generator\StateHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelDumper;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelDumperInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Selenium\Helper;
use Tienvx\Bundle\MbtBundle\Service\Selenium\HelperInterface;
use Tienvx\Bundle\MbtBundle\Service\CommandRunner;
use Tienvx\Bundle\MbtBundle\Service\CommandRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStepsBuilder;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStrategyInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunner;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunner;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\TaskProgress;
use Tienvx\Bundle\MbtBundle\Service\TaskProgressInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Provider\Selenoid;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(ChannelManager::class)
        ->set(EmailChannel::class)
        ->set(NexmoChannel::class)
        ->set(SlackChannel::class)
        ->set(TelegramChannel::class)
        ->set(TwilioChannel::class)

        ->set(EntitySubscriber::class)
            ->tag('doctrine.event_subscriber')
            ->args([
                service(MessageBusInterface::class),
            ])

        ->set(GeneratorManager::class)

        ->set(RandomGenerator::class)
            ->args([
                service(PetrinetHelperInterface::class),
                service(MarkingHelperInterface::class),
                service(ModelHelperInterface::class),
                service(GuardedTransitionServiceInterface::class),
                service(StateHelperInterface::class),
            ])

        ->set(ProviderManager::class)
        ->set(Selenoid::class)

        ->set(ExecuteTaskMessageHandler::class)
            ->args([
                service(GeneratorManager::class),
                service(EntityManagerInterface::class),
                service(StepsRunnerInterface::class),
                service(ConfigLoaderInterface::class),
                service(TaskProgressInterface::class),
                service(BugHelperInterface::class),
            ])

        ->set(ReduceBugMessageHandler::class)
            ->args([
                service(ReducerManager::class),
                service(EntityManagerInterface::class),
                service(MessageBusInterface::class),
                service(ConfigLoaderInterface::class),
                service(BugProgressInterface::class),
            ])

        ->set(ReduceStepsMessageHandler::class)
            ->args([
                service(ReducerManager::class),
                service(EntityManagerInterface::class),
                service(MessageBusInterface::class),
                service(ConfigLoaderInterface::class),
                service(BugProgressInterface::class),
            ])

        ->set(ReportBugMessageHandler::class)
            ->args([
                service(EntityManagerInterface::class),
                service(NotifierInterface::class),
                service(ConfigLoaderInterface::class),
                service(BugHelperInterface::class),
                service(TranslatorInterface::class),
            ])

        ->set(ReducerManager::class)
        ->set(RandomDispatcher::class)
            ->args([
                service(MessageBusInterface::class),
            ])
        ->set(RandomHandler::class)
            ->args([
                service(EntityManagerInterface::class),
                service(MessageBusInterface::class),
                service(StepsRunnerInterface::class),
                service(StepsBuilderInterface::class),
            ])
        ->set(RandomReducer::class)
            ->args([
                service(RandomDispatcher::class),
                service(RandomHandler::class),
            ])
        ->set(SplitDispatcher::class)
            ->args([
                service(MessageBusInterface::class),
            ])
        ->set(SplitHandler::class)
            ->args([
                service(EntityManagerInterface::class),
                service(MessageBusInterface::class),
                service(StepsRunnerInterface::class),
                service(StepsBuilderInterface::class),
            ])
        ->set(SplitReducer::class)
            ->args([
                service(SplitDispatcher::class),
                service(SplitHandler::class),
            ])

        // Services
        ->set(ConfigLoaderInterface::class)
        ->set(ExpressionLanguage::class)

        ->set(ModelDumper::class)
            ->alias(ModelDumperInterface::class, ModelDumper::class)
        ->set(ModelHelper::class)
            ->alias(ModelHelperInterface::class, ModelHelper::class)

        ->set(BugHelper::class)
            ->args([
                service(TranslatorInterface::class),
            ])
            ->alias(BugHelperInterface::class, BugHelper::class)

        ->set(BugProgress::class)
            ->args([
                service(EntityManagerInterface::class),
            ])
            ->alias(BugProgressInterface::class, BugProgress::class)

        ->set(Helper::class)
            ->alias(HelperInterface::class, Helper::class)

        ->set(CommandRunner::class)
            ->args([
                service(HelperInterface::class),
            ])
            ->alias(CommandRunnerInterface::class, CommandRunner::class)

        ->set(ShortestPathStepsBuilder::class)
            ->args([
                service(PetrinetHelperInterface::class),
                service(ShortestPathStrategyInterface::class),
            ])
            ->alias(StepsBuilderInterface::class, ShortestPathStepsBuilder::class)

        ->set(AStarStrategy::class)
            ->args([
                service(GuardedTransitionServiceInterface::class),
                service(MarkingHelperInterface::class),
            ])
            ->alias(ShortestPathStrategyInterface::class, AStarStrategy::class)

        ->set(StepRunner::class)
            ->args([
                service(CommandRunnerInterface::class),
            ])
            ->alias(StepRunnerInterface::class, StepRunner::class)

        ->set(StepsRunner::class)
            ->args([
                service(PetrinetHelperInterface::class),
                service(MarkingHelperInterface::class),
                service(GuardedTransitionServiceInterface::class),
                service(StepRunnerInterface::class),
            ])
            ->alias(StepsRunnerInterface::class, StepsRunner::class)

        ->set(TaskProgress::class)
            ->alias(TaskProgressInterface::class, TaskProgress::class)

        ->set(MarkingHelper::class)
            ->args([
                service(ColorfulFactoryInterface::class),
            ])
            ->alias(MarkingHelperInterface::class, MarkingHelper::class)

        ->set(PetrinetHelper::class)
            ->args([
                service(ColorfulFactoryInterface::class),
            ])
            ->alias(PetrinetHelperInterface::class, PetrinetHelper::class)

        ->set(StateHelper::class)
            ->args([
                service(ConfigLoaderInterface::class),
            ])
            ->alias(StateHelperInterface::class, StateHelper::class)

        // Single Color Petrinet services
        ->set(ExpressionLanguageEvaluator::class)
            ->args([
                service(ExpressionLanguage::class),
            ])
            ->alias(ExpressionEvaluatorInterface::class, ExpressionLanguageEvaluator::class)

        ->set(ColorfulFactory::class)
            ->alias(ColorfulFactoryInterface::class, ColorfulFactory::class)

        ->set(GuardedTransitionService::class)
            ->args([
                service(ColorfulFactoryInterface::class),
                service(ExpressionEvaluatorInterface::class),
            ])
            ->alias(GuardedTransitionServiceInterface::class, GuardedTransitionService::class)

        ->set(MarkingBuilder::class)
            ->args([
                service(ColorfulFactoryInterface::class),
            ])
    ;
};

<?php

use Doctrine\ORM\EntityManagerInterface;
use Petrinet\Builder\MarkingBuilder;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Service\ExpressionEvaluatorInterface;
use SingleColorPetrinet\Service\ExpressionLanguageEvaluator;
use SingleColorPetrinet\Service\GuardedTransitionService;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
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
use Tienvx\Bundle\MbtBundle\Service\BugSubscriberInterface;
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
use Tienvx\Bundle\MbtBundle\Service\Selenium;
use Tienvx\Bundle\MbtBundle\Service\SeleniumInterface;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStepsBuilder;
use Tienvx\Bundle\MbtBundle\Service\ShortestPathStrategyInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunner;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunner;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\TaskProgress;
use Tienvx\Bundle\MbtBundle\Service\TaskProgressInterface;

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
                new Reference(MessageBusInterface::class),
            ])

        ->set(GeneratorManager::class)

        ->set(RandomGenerator::class)
            ->args([
                new Reference(PetrinetHelperInterface::class),
                new Reference(MarkingHelperInterface::class),
                new Reference(ModelHelperInterface::class),
                new Reference(GuardedTransitionServiceInterface::class),
                new Reference(StateHelperInterface::class),
            ])

        ->set(ExecuteTaskMessageHandler::class)
            ->args([
                new Reference(GeneratorManager::class),
                new Reference(EntityManagerInterface::class),
                new Reference(StepsRunnerInterface::class),
                new Reference(ConfigLoaderInterface::class),
                new Reference(TaskProgressInterface::class),
                new Reference(BugHelperInterface::class),
            ])

        ->set(ReduceBugMessageHandler::class)
            ->args([
                new Reference(ReducerManager::class),
                new Reference(EntityManagerInterface::class),
                new Reference(MessageBusInterface::class),
                new Reference(ConfigLoaderInterface::class),
                new Reference(BugProgressInterface::class),
            ])

        ->set(ReduceStepsMessageHandler::class)
            ->args([
                new Reference(ReducerManager::class),
                new Reference(EntityManagerInterface::class),
                new Reference(MessageBusInterface::class),
                new Reference(ConfigLoaderInterface::class),
                new Reference(BugProgressInterface::class),
            ])

        ->set(ReportBugMessageHandler::class)
            ->args([
                new Reference(EntityManagerInterface::class),
                new Reference(NotifierInterface::class),
                new Reference(ConfigLoaderInterface::class),
                new Reference(BugSubscriberInterface::class),
                new Reference(BugHelperInterface::class),
                new Reference(TranslatorInterface::class),
            ])

        ->set(ReducerManager::class)
        ->set(RandomDispatcher::class)
            ->args([
                new Reference(MessageBusInterface::class),
            ])
        ->set(RandomHandler::class)
            ->args([
                new Reference(EntityManagerInterface::class),
                new Reference(MessageBusInterface::class),
                new Reference(StepsRunnerInterface::class),
                new Reference(StepsBuilderInterface::class),
            ])
        ->set(RandomReducer::class)
            ->args([
                new Reference(RandomDispatcher::class),
                new Reference(RandomHandler::class),
            ])
        ->set(SplitDispatcher::class)
            ->args([
                new Reference(MessageBusInterface::class),
            ])
        ->set(SplitHandler::class)
            ->args([
                new Reference(EntityManagerInterface::class),
                new Reference(MessageBusInterface::class),
                new Reference(StepsRunnerInterface::class),
                new Reference(StepsBuilderInterface::class),
            ])
        ->set(SplitReducer::class)
            ->args([
                new Reference(SplitDispatcher::class),
                new Reference(SplitHandler::class),
            ])

        // Services
        ->set(BugSubscriberInterface::class)
        ->set(ConfigLoaderInterface::class)
        ->set(ExpressionLanguage::class)

        ->set(ModelDumper::class)
            ->alias(ModelDumperInterface::class, ModelDumper::class)
        ->set(ModelHelper::class)
            ->alias(ModelHelperInterface::class, ModelHelper::class)

        ->set(BugHelper::class)
            ->args([
                new Reference(TranslatorInterface::class),
            ])
            ->alias(BugHelperInterface::class, BugHelper::class)

        ->set(BugProgress::class)
            ->args([
                new Reference(EntityManagerInterface::class),
            ])
            ->alias(BugProgressInterface::class, BugProgress::class)

        ->set(Selenium::class)
            ->args([
                new Reference(ConfigLoaderInterface::class),
            ])
            ->alias(SeleniumInterface::class, Selenium::class)

        ->set(ShortestPathStepsBuilder::class)
            ->args([
                new Reference(PetrinetHelperInterface::class),
                new Reference(ShortestPathStrategyInterface::class),
            ])
            ->alias(StepsBuilderInterface::class, ShortestPathStepsBuilder::class)

        ->set(AStarStrategy::class)
            ->args([
                new Reference(GuardedTransitionServiceInterface::class),
                new Reference(MarkingHelperInterface::class),
            ])
            ->alias(ShortestPathStrategyInterface::class, AStarStrategy::class)

        ->set(StepRunner::class)
            ->args([
                new Reference(SeleniumInterface::class),
            ])
            ->alias(StepRunnerInterface::class, StepRunner::class)

        ->set(StepsRunner::class)
            ->args([
                new Reference(PetrinetHelperInterface::class),
                new Reference(MarkingHelperInterface::class),
                new Reference(GuardedTransitionServiceInterface::class),
                new Reference(StepRunnerInterface::class),
            ])
            ->alias(StepsRunnerInterface::class, StepsRunner::class)

        ->set(TaskProgress::class)
            ->alias(TaskProgressInterface::class, TaskProgress::class)

        ->set(MarkingHelper::class)
            ->args([
                new Reference(ColorfulFactoryInterface::class),
            ])
            ->alias(MarkingHelperInterface::class, MarkingHelper::class)

        ->set(PetrinetHelper::class)
            ->args([
                new Reference(ColorfulFactoryInterface::class),
            ])
            ->alias(PetrinetHelperInterface::class, PetrinetHelper::class)

        ->set(StateHelper::class)
            ->args([
                new Reference(ConfigLoaderInterface::class),
            ])
            ->alias(StateHelperInterface::class, StateHelper::class)

        // Single Color Petrinet services
        ->set(ExpressionLanguageEvaluator::class)
            ->args([
                new Reference(ExpressionLanguage::class),
            ])
            ->alias(ExpressionEvaluatorInterface::class, ExpressionLanguageEvaluator::class)

        ->set(ColorfulFactory::class)
            ->alias(ColorfulFactoryInterface::class, ColorfulFactory::class)

        ->set(GuardedTransitionService::class)
            ->args([
                new Reference(ColorfulFactoryInterface::class),
                new Reference(ExpressionEvaluatorInterface::class),
            ])
            ->alias(GuardedTransitionServiceInterface::class, GuardedTransitionService::class)

        ->set(MarkingBuilder::class)
            ->args([
                new Reference(ColorfulFactoryInterface::class),
            ])
    ;
};

<?php

namespace Tienvx\Bundle\MbtBundle\Maker;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

final class MakeSubject extends AbstractMaker
{
    /**
     * @var Registry
     */
    private $workflowRegistry;

    /**
     * @var SubjectManager
     */
    private $subjectManager;

    public function __construct(Registry $workflowRegistry, SubjectManager $subjectManager)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager = $subjectManager;
    }

    public static function getCommandName(): string
    {
        return 'make:subject';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->setDescription('Creates a new subject class for a model')
            ->addArgument('model', InputArgument::OPTIONAL, 'The model to generate subject.')
            ->addArgument('subject-class', InputArgument::OPTIONAL, sprintf('Choose a name for your subject class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeSubject.txt'))
        ;
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle   $io
     * @param Generator      $generator
     *
     * @throws \Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $model = $input->getArgument('model');
        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);

        if ($this->subjectManager->hasSubject($model)) {
            $subject = $this->subjectManager->getSubject($model);
            if (class_exists($subject)) {
                $io->text(sprintf('The subject for model %s has been already defined: %s!', $model, $subject));

                return;
            }
        } else {
            $subject = $input->getArgument('subject-class');
        }

        $subjectClassNameDetails = $generator->createClassNameDetails(
            $subject,
            'Subject\\'
        );

        $methods = [];
        foreach ($workflow->getDefinition()->getPlaces() as $place => $status) {
            if ($status) {
                $methods[] = $place;
            }
        }
        foreach ($workflow->getDefinition()->getTransitions() as $transition) {
            $methods[] = $transition->getName();
        }

        $generator->generateClass(
            $subjectClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/subject/Subject.php.tpl',
            [
                'methods' => array_unique($methods),
                'model' => $model,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: Open the new generated subject class and implement places and transitions!',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            // we only need doctrine/annotations, which contains
            // the recipe that loads annotation data providers
            Annotation::class,
            'annotations'
        );
    }
}

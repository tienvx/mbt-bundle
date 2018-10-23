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
use Tienvx\Bundle\MbtBundle\Subject\Subject;
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

    public function __construct(SubjectManager $subjectManager)
    {
        $this->subjectManager = $subjectManager;
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
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
     * @param ConsoleStyle $io
     * @param Generator $generator
     * @throws \Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $model = $input->getArgument('model');
        $subject = new class extends Subject {
        };
        $workflow = $this->workflowRegistry->get($subject, $model);

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
            __DIR__.'/../Resources/skeleton/subject/Subject.tpl.php',
            [
                'methods' => array_unique($methods),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: Update configuration file at tienvx_mbt.subjects, add this line:',
            sprintf('%s: %s', $model, $subjectClassNameDetails->getFullName()),
            'Then: Open your new subject class and implement places and transitions!'
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

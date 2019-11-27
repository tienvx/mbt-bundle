<?php

namespace Tienvx\Bundle\MbtBundle\Maker;

use Doctrine\Common\Annotations\Annotation;
use Exception;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

final class MakeSubject extends AbstractMaker
{
    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

    public function __construct(WorkflowHelper $workflowHelper)
    {
        $this->workflowHelper = $workflowHelper;
    }

    public static function getCommandName(): string
    {
        return 'make:subject';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf): void
    {
        $command
            ->setDescription('Creates a new subject class for a model')
            ->addArgument('model', InputArgument::OPTIONAL, 'The model to generate subject.')
            ->addArgument('subject-class', InputArgument::OPTIONAL, sprintf('Choose a name for your subject class (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeSubject.txt'))
        ;
    }

    /**
     * @throws Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $model = $input->getArgument('model');
        $workflow = $this->workflowHelper->get($model);
        $subjectClass = $input->getArgument('subject-class');

        $this->generateClass($generator, $subjectClass, $workflow, $model);

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: Open the new generated subject class and implement places and transitions!',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        $dependencies->addClassDependency(
            // we only need doctrine/annotations, which contains
            // the recipe that loads annotation data providers
            Annotation::class,
            'annotations'
        );
    }

    /**
     * @see http://www.mendoweb.be/blog/php-convert-string-to-camelcase-string/
     */
    private function camelCase(string $str): string
    {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);

        return lcfirst($str);
    }

    private function getPlaces(Workflow $workflow): array
    {
        $places = [];
        foreach ($workflow->getDefinition()->getPlaces() as $place => $status) {
            if ($status) {
                $places[$place] = $this->camelCase($place);
            }
        }

        return $places;
    }

    private function getTransitions(Workflow $workflow): array
    {
        $transitions = [];
        foreach ($workflow->getDefinition()->getTransitions() as $transition) {
            $transitions[$transition->getName()] = $this->camelCase($transition->getName());
        }

        return $transitions;
    }

    private function generateClass(Generator $generator, string $subjectClass, Workflow $workflow, string $model): void
    {
        $subjectClassNameDetails = $generator->createClassNameDetails(
            $subjectClass,
            'Subject\\'
        );

        $generator->generateClass(
            $subjectClassNameDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/subject/Subject.php.tpl',
            [
                'places' => $this->getPlaces($workflow),
                'transitions' => $this->getTransitions($workflow),
                'model' => $model,
            ]
        );

        $generator->writeChanges();
    }
}

<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Generator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class <?= $class_name; ?> extends AbstractGenerator
{
    public static function getName(): string
    {
        return '<?= $name; ?>';
    }

    public function getLabel(): string
    {
        return '';
    }

    /**
     * @param Workflow         $workflow
     * @param SubjectInterface $subject
     * @param GeneratorOptions $generatorOptions
     *
     * @return Generator
     */
    public function getAvailableTransitions(Workflow $workflow, SubjectInterface $subject, GeneratorOptions $generatorOptions = null): Generator
    {
        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $workflow->getEnabledTransitions($subject);
            if (!empty($transitions)) {
                // TODO This code always generate the first transition, never stop, change this to fit your requirements
                $transition = reset($transitions);

                yield $transition->getName();
            } else {
                break;
            }
        }
    }
}

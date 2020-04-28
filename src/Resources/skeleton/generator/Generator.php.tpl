<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;

class <?= $class_name; ?> implements GeneratorInterface
{
    public static function getName(): string
    {
        return '<?= $name; ?>';
    }

    public function getLabel(): string
    {
        return '';
    }

    public static function support(): bool
    {
        return true;
    }

    public function generate(Workflow $workflow, SubjectInterface $subject, GeneratorOptions $generatorOptions = null): iterable
    {
        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $workflow->getEnabledTransitions($subject);
            if (!empty($transitions)) {
                // TODO This code always generate the first transition, never stop, change this to fit your requirements
                $transition = reset($transitions);

                yield new Step($$transition->getName(), new Data());
            } else {
                break;
            }
        }
    }
}

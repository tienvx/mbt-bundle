<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Workflow\Transition;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

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

    public function generate(Model $model, SubjectInterface $subject, GeneratorOptions $generatorOptions = null): iterable
    {
        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $model->getEnabledTransitions($subject);
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

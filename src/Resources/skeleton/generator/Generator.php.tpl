<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Generator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class <?= $class_name; ?> extends AbstractGenerator
{
    public static function getName()
    {
        return '<?= $name; ?>';
    }

    /**
     * @param Workflow        $workflow
     * @param AbstractSubject $subject
     * @param array           $metaData
     *
     * @return Generator
     */
    public function getAvailableTransitions(Workflow $workflow, AbstractSubject $subject, array $metaData = null): Generator
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
<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Reducer\AbstractReducer;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;

class <?= $class_name; ?> extends AbstractReducer
{
    public static function getName(): string
    {
        return '<?= $name; ?>';
    }

    public function getLabel(): string
    {
        return '';
    }

    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to)
    {
        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }

    public function dispatch(Bug $bug): int
    {
        $message = new ReduceStepsMessage($bugId, static::getName(), 0, 0, 0);
        $this->messageBus->dispatch($message);
        return 1;
    }
}

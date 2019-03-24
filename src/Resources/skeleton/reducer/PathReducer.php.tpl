<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\PathReducer\AbstractPathReducer;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;

class <?= $class_name; ?> extends AbstractPathReducer
{
    public static function getName()
    {
        return '<?= $name; ?>';
    }

    public function handle(ReductionMessage $message)
    {
        $this->postHandle($message);
    }

    public function dispatch(int $bugId, Path $newPath = null, ReductionMessage $message = null): int
    {
        $message = new ReductionMessage($bugId, static::getName(), []);
        $this->messageBus->dispatch($message);
        return 1;
    }
}

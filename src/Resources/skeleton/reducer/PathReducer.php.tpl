<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Tienvx\Bundle\MbtBundle\Entity\Path;
use Tienvx\Bundle\MbtBundle\PathReducer\AbstractPathReducer;
use Tienvx\Bundle\MbtBundle\Message\ReducePathMessage;

class <?= $class_name; ?> extends AbstractPathReducer
{
    public static function getName(): string
    {
        return '<?= $name; ?>';
    }

    public function getLabel(): string
    {
        return '';
    }

    public function handle(int $bugId, int $length, int $from, int $to)
    {
        $this->postHandle($bugId);
    }

    public function dispatch(int $bugId, Path $newPath = null): int
    {
        $message = new ReducePathMessage($bugId, static::getName(), 0, 0, 0);
        $this->messageBus->dispatch($message);
        return 1;
    }
}

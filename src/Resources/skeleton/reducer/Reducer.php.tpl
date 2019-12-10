<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Tienvx\Bundle\MbtBundle\Reducer\DispatcherInterface;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerTemplate;

class <?= $class_name; ?> extends ReducerTemplate
{
//    public function __construct(DispatcherInterface $dispatcher, HandlerInterface $handler)
//    {
//        $this->dispatcher = $dispatcher;
//        $this->handler = $handler;
//    }

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
}

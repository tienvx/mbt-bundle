<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Tienvx\Bundle\MbtBundle\Reporter\ReporterInterface;

class <?= $class_name; ?> implements ReporterInterface
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
}

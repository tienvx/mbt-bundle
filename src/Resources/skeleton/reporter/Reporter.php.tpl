<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Tienvx\Bundle\MbtBundle\Entity\Bug;
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

    /**
     * @param Bug $bug
     */
    public function report(Bug $bug)
    {
        // $this->sendBugReport($bug);
    }
}

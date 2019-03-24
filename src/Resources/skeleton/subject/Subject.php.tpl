<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class <?= $class_name; ?> extends AbstractSubject
{
    public static function support(): string
    {
        return '<?= $model; ?>';
    }

<?php foreach ($methods as $index => $method): ?>
    public function <?= $method; ?>()
    {
    }
<?= $index < (count($methods) - 1) ? "\n" : ''; ?>
<?php endforeach; ?>
}

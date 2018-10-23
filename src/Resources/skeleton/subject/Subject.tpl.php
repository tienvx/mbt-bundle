<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Tienvx\Bundle\MbtBundle\Subject\Subject;

class <?= $class_name; ?> extends Subject
{
<?php foreach ($methods as $index => $method): ?>
    public function <?= $method; ?>()
    {
    }
<?= $index < (count($methods) - 1) ? "\n" : ''; ?>
<?php endforeach; ?>
}

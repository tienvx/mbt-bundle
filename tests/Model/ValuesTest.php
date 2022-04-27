<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Values;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Values
 */
class ValuesTest extends TestCase
{
    public function testGetValues(): void
    {
        $values = new Values(['key' => 'value', 'key2' => 'value2']);
        $this->assertSame(['key' => 'value', 'key2' => 'value2'], $values->getValues());
    }

    public function testGetValue(): void
    {
        $values = new Values(['key' => 'value']);
        $this->assertSame('value', $values->getValue('key'));
        $this->assertNull($values->getValue('key2'));
    }

    public function testSetValue(): void
    {
        $values = new Values();
        $this->assertSame([], $values->getValues());
        $values->setValue('key', 'value');
        $values->setValue('key2', null);
        $values->setValue('key3', 123);
        $values->setValue('key4', json_decode('{"test1":"Test 1","test2":{},"test3":{"test4":"Test 4"}}'));
        $this->assertSame(
            '{"key":"value","key2":null,"key3":123,"key4":{"test1":"Test 1","test2":{},"test3":{"test4":"Test 4"}}}',
            json_encode($values->getValues())
        );
    }
}

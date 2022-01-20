<?php
namespace Littled\Tests\Ajax;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Ajax\JSONField;
use Littled\Tests\Ajax\Samples\JSONResponseSample;
use PHPUnit\Framework\TestCase;

class JSONFieldTest extends TestCase
{
    public function testFormatJsonInteger()
    {
        $field = new JSONField('test', 2);
        $arr = array();
        $field->formatJSON($arr);
        $this->assertArrayHasKey('test', $arr);
        $this->assertEquals(2, $arr['test']);
    }

    public function testFormatJsonString()
    {
        $field = new JSONField('string_test', 'my test string');
        $arr = array();
        $field->formatJSON($arr);
        $this->assertArrayHasKey('string_test', $arr);
        $this->assertEquals('my test string', $arr['string_test']);
    }

    public function testFormatJsonArray()
    {
        $field = new JSONField('array_test', array(4,6,2));
        $arr = array();
        $field->formatJSON($arr);
        $this->assertArrayHasKey('array_test', $arr);
        $this->assertIsArray($arr['array_test']);
        $this->assertEqualsCanonicalizing(array(4,6,2), $arr['array_test']);
    }

    public function testFormatJsonObjectArray()
    {
        $s1 = new JSONResponseSample();
        $s1->f1->value = 'edit one';
        $s2 = new JSONResponseSample();
        $s2->f2->value = 'edit two';
        $src_arr = array($s1, $s2);
        $field = new JSONField('object_test', $src_arr);

        $result = Array();
        $field->formatJSON($result);

        $this->assertArrayHasKey('object_test', $result);
        $this->assertIsArray($result['object_test']);
        $this->assertCount(2, $result['object_test']);

        $this->assertIsObject($result['object_test'][0]);
        $this->assertObjectHasAttribute('field_1', $result['object_test'][0]);
        $this->assertEquals('edit one', $result['object_test'][0]->field_1);

        $this->assertIsObject($result['object_test'][1]);
        $this->assertObjectHasAttribute('field_1', $result['object_test'][1]);
        $this->assertEquals('test value one', $result['object_test'][1]->field_1);
        $this->assertEquals('edit two', $result['object_test'][1]->field_2);
    }
}

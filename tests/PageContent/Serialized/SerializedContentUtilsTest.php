<?php
namespace Littled\Tests\PageContent\Serialized;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\Serialized\SerializedContentUtils;
use Littled\Request\RequestInput;
use Littled\Request\BooleanInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;
use PHPUnit\Framework\TestCase;


/**
 * Class SerializedContentUtilsChild
 * @package Littled\Tests\PageContent
 */
class SerializedContentUtilsChild extends SerializedContent
{
	public $vc_col1;
	public $vc_col2;
	public $int_col;
	public $bool_col;
	public $prop1;
	public $prop2;
	protected static $cache_template = "/path/to/templates/child-cache-template.php";

	const SECTION_ID = 10; /* sketchbook page */

	/**
	 * SerializedContentUtilsChild constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->vc_col1 = new StringInput('Test varchar value 1', 'p_vc1', true, '', 50);
		$this->vc_col2 = new StringInput('Test varchar value 1', 'p_vc2', false, '', 255);
		$this->int_col = new IntegerInput('Test int value', 'p_int');
		$this->bool_col = new BooleanInput('Test bool value', 'p_bool');
	}

	public static function TABLE_NAME(): string
	{
		return ('serialized_content_utils_unit_tests');
	}

	public function hasData(): bool
	{
		if ($this->vc_col2->value !== null && strlen($this->vc_col2->value) > 0) { return(true); }
		if ($this->vc_col1->value !== null && strlen($this->vc_col1->value) > 0) { return(true); }
		if ($this->int_col->value !== null) { return(true); }
		if ($this->bool_col->value !== null) { return(true); }
		return (parent::hasData());
	}
}

class SerializedContentChild extends SerializedContent
{
	public $bool_field;
	public $str_field;
	/** @var SerializedContentUtilsChild */
	public $child;

	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->str_field = new StringInput('String Field', 'str_param', true,'test value');
		$this->bool_field = new BooleanInput('Boolean Field', 'bool_param', true, true);
		$this->child = null;
	}
}


class SerializedContentUtilsTest extends TestCase
{
	/** @var SerializedContentUtilsChild Test object. */
	public $obj;
	/** @var MySQLConnection Database connection. */
	public $conn;

	public const TEST_SOURCE_TEMPLATE = "serialized-content-source-template.php";
	public const TEST_OUTPUT_TEMPLATE = "serialized-content-output-template.php";

	/**
	 * @throws NotImplementedException Table name is not set in inherited classes.
	 * @throws InvalidQueryException Error executing query.
	 */
	public static function setUpBeforeClass(): void
	{
		$c = new MySQLConnection();

		$query = "DROP TABLE IF EXISTS `".SerializedContentUtilsChild::TABLE_NAME()."`; ".
			"CREATE TABLE `".SerializedContentUtilsChild::TABLE_NAME()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`vc_col1` VARCHAR(50),".
			"`vc_col2` VARCHAR(255),".
			"`int_col` INT,".
			"`bool_col` BOOLEAN);";
		$c->query($query);
	}

	/**
	 * @throws NotImplementedException Table name is not set in inherited classes.
	 * @throws InvalidQueryException Error executing query.
	 */
	public static function tearDownAfterClass(): void
	{
		$c = new MySQLConnection();
		$query = "DR"."OP TABLE `".SerializedContentUtilsChild::TABLE_NAME()."`";
		$c->query($query);
	}

	public function setUp(): void
	{
		$this->obj = new SerializedContent();
		$this->conn = new MySQLConnection();
	}

	public function testAppendDelimiter()
    {
        $obj = new SerializedContent();
        self::assertEquals("abc, ", $obj->appendSeparator('abc'));
        self::assertEquals('abc: ', $obj->appendSeparator('abc', ':'));
        self::assertEquals('abcnnn ', $obj->appendSeparator('abc', 'nnn'));
        self::assertEquals('', $obj->appendSeparator(''));
        self::assertEquals('foo ', $obj->appendSeparator('foo', ''));
    }

	public function testArrayEncode()
	{
		$obj = new SerializedContentUtilsChild();
		$ar = $obj->arrayEncode();
		$this->assertArrayHasKey('id', $ar);
		$this->assertNull($ar['id']);

		/* integer property */
		$obj->id->value = 45;
		$ar = $obj->arrayEncode();
		$this->assertArrayHasKey('id', $ar);
		$this->assertEquals(45, $ar['id']);

		/* default string property value */
		$this->assertArrayHasKey('vc_col1', $ar);
		$this->assertEquals('', $ar['vc_col1']);

		/* string value */
		$obj->vc_col2->value = 'fookazi';
		$ar = $obj->arrayEncode();
		$this->assertEquals('fookazi', $ar['vc_col2']);

		$this->assertArrayNotHasKey('prop1', $ar);

		/* non-RequestInput property */
		$obj->prop1 = 78;
		$ar = $obj->arrayEncode();
		$this->assertArrayNotHasKey('prop1', $ar);

		/* bool property */
		$this->assertArrayHasKey('bool_col', $ar);
		$this->assertNull($ar['bool_col']);

		$obj->bool_col->setInputValue(true);
		$ar = $obj->arrayEncode();
		$this->assertTrue($ar['bool_col']);

		$obj->bool_col->setInputValue(false);
		$ar = $obj->arrayEncode();
		$this->assertFalse($ar['bool_col']);
	}

	public function testClearValues()
	{
		$obj = new SerializedContentChild();

		/* default values */
		$obj->clearValues();
		$this->assertEquals(null, $obj->id->value);

		/* clear id property value */
		$obj->id->value = 66;
		$obj->clearValues();
		$this->assertEquals(null, $obj->id->value);

		/* clear string property value */
		$obj->id->value = 78;
		$this->assertEquals(78, $obj->id->value);
		$this->assertEquals('', $obj->str_field->value);

		$obj->clearValues();
		$this->assertEquals(null, $obj->id->value);
		$this->assertEquals('', $obj->str_field->value);

		/* clear boolean property value */
		$obj->str_field->value = 'foo';
		$this->assertNull($obj->bool_field->value);
		$this->assertEquals('foo', $obj->str_field->value);

		$obj->clearValues();
		self::assertNull($obj->id->value);
		self::assertEquals(null, $obj->bool_field->value);
		self::assertEquals('', $obj->str_field->value);

		/* clear child object properties recursively */
		$c = new SerializedContentUtilsChild();
		$c->id->value = 98;
		$c->vc_col2->value = 'biz';
		$obj->child = $c;
		$obj->id->value = 103;
		$this->assertEquals(103, $obj->id->value);
		$this->assertEquals(98, $obj->child->id->value);
		$this->assertEquals('biz', $obj->child->vc_col2->value);

		$obj->clearValues();
		$this->assertNull($obj->id->value);
		$this->assertNull($obj->child->id->value);
		$this->assertEquals('', $obj->child->vc_col2->value);
	}

    /**
     * @throws InvalidTypeException
     */
	public function testCopy()
    {
        $src = new SerializedContentUtilsChild();
        $dst = new SerializedContentUtilsChild();

        $src->vc_col1->value = "foo";
        $src->vc_col2->value = "bar";
        $src->prop2 = 78;

        $dst->copy($src);
        $this->assertEquals($src->id->value, $dst->id->value);
        $this->assertEquals($src->vc_col1->value, $dst->vc_col1->value);
        $this->assertEquals($src->prop2, $dst->prop2);
        $this->assertEquals($src->vc_col2->value, $dst->vc_col2->value);
        $this->assertEquals('foo', $src->vc_col1->value);

        $dst->vc_col1->value = 'biz';
        $this->assertNotEquals($src->vc_col1->value, $dst->vc_col1->value);

        $src->prop1 = array(5, 6, 3, 9, 2);
        $dst->copy($src);
        $this->assertEquals(3, $dst->prop1[2]);

        $dst->prop1[2] = 4;
        $this->assertNotEquals($src->prop1[2], $dst->prop1[2]);
    }

	public function testFill()
	{
		$dst = new SerializedContentUtilsChild();
		$src = array('id' => 22,
			'vc_col1' => 'foo',
			'int_col' => 482,
			'invalid_key' => 'some arbitrary value',
			'vc_col2' => 'biz',
			'bool_col' => false);
		$dst->fill($src);
		$this->assertEquals(22, $dst->id->value);
		$this->assertEquals('foo', $dst->vc_col1->value);
		$this->assertEquals('biz', $dst->vc_col2->value);
		$this->assertEquals(482, $dst->int_col->value);
		$this->assertFalse($dst->bool_col->value);

		/* test assigning non-boolean value to boolean field */
		$src['bool_col'] = 'dingdong';
		$dst->fill($src);
		$this->assertNull($dst->bool_col->value);

		/* test assigning non-boolean value to boolean field */
		$src['int_col'] = 'dingdong';
		$dst->fill($src);
		$this->assertNull($dst->int_col->value);
	}

    public function testGetContentTypeID()
    {
    	$obj = new SerializedContentUtilsChild();
    	$this->assertEquals(SerializedContentUtilsChild::SECTION_ID, $obj->getContentTypeID());
    }

	public function testGetContentTypeIDWithConstNotDefined()
	{
		$this->assertNull($this->obj->getContentTypeID());
	}

	public function testJsonEncode()
	{
		$obj = new SerializedContentUtilsChild();
		$obj->vc_col1->setInputValue("foo");
		$obj->vc_col2->setInputValue("bar");
		$obj->int_col->setInputValue(784);
		$obj->bool_col->setInputValue(true);

		$json_str = $obj->jsonEncode();
		$this->assertStringContainsString("\"vc_col1\":\"foo\"", $json_str);
		$this->assertStringContainsString("\"vc_col2\":\"bar\"", $json_str);
		$this->assertStringContainsString("\"int_col\":784", $json_str);
		$this->assertStringContainsString("\"bool_col\":true", $json_str);
	}

	public function testJsonEncodeDefaultValues()
	{
		$obj = new SerializedContentUtilsChild();

		$json_str = $obj->jsonEncode();
		$this->assertStringContainsString("\"vc_col1\":\"\"", $json_str);
		$this->assertStringContainsString("\"vc_col2\":\"\"", $json_str);
		$this->assertStringContainsString("\"int_col\":null", $json_str);
		$this->assertStringContainsString("\"bool_col\":null", $json_str);
	}

	public function testJsonEncodeNonObjectProperty()
	{
		$obj = new SerializedContentUtilsChild();

		$json_str = $obj->jsonEncode();
		$this->assertStringNotContainsString("\"prop1\"", $json_str);
	}

	public function testJsonEncodeExcludeKeys()
	{
		$obj = new SerializedContentUtilsChild();
		$exclude_keys = ['vc_col2', 'bool_col'];

		$json_str = $obj->jsonEncode($exclude_keys);
		$this->assertStringContainsString("\"vc_col1\"", $json_str);
		$this->assertStringNotContainsString("\"vc_col2\"", $json_str);
		$this->assertStringContainsString("\"int_col\"", $json_str);
		$this->assertStringNotContainsString("\"bool_col\"", $json_str);
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws InvalidValueException
	 */
	public function testPluralLabel()
	{
		$obj = new SerializedContentUtilsChild();

		// test null property value
		$this->expectException(InvalidValueException::class);
		$obj->pluralLabel(1, 'vc_col1');

		$obj->vc_col1->setInputValue('thing');
		$this->assertEquals('thing', $obj->pluralLabel(1, 'vc_col1'));
		$this->assertEquals('things', $obj->pluralLabel(2, 'vc_col1'));
		$this->assertEquals('things', $obj->pluralLabel(0, 'vc_col1'));

		$obj->vc_col1->setInputValue('thingy');
		$this->assertEquals('thingy', $obj->pluralLabel(1, 'vc_col1'));
		$this->assertEquals('thingies', $obj->pluralLabel(28, 'vc_col1'));
		$this->assertEquals('thingies', $obj->pluralLabel(0, 'vc_col1'));
	}

	/**
	 * @throws InvalidValueException
	 * @throws ConfigurationUndefinedException
	 */
	public function testPluralLabelInvalidArguments()
	{
		$obj = new SerializedContentUtilsChild();

		/* non-existent property */
		$this->expectException(ConfigurationUndefinedException::class);
		$obj->pluralLabel(2, 'not_a_property');

		/* non-string property */
		$this->assertNull($obj->pluralLabel(2, 'int_col'));

		/* default string property value */
		$this->assertNull($obj->pluralLabel(2, 'vc_col1'));

		/* default string property value */
		$obj->vc_col1->setInputValue('');
		$this->assertNull($obj->pluralLabel(2, 'vc_col1'));
	}

    public function testPrependDelimiter()
    {
        $obj = new SerializedContent();
        self::assertEquals(", abc", $obj->prependSeparator('abc'));
        self::assertEquals(': abc', $obj->prependSeparator('abc', ':'));
        self::assertEquals('nnn abc', $obj->prependSeparator('abc', 'nnn'));
        self::assertEquals('', $obj->prependSeparator(''));
        self::assertEquals(' foo', $obj->prependSeparator('foo', ''));
    }

	public function testPreserveInForm()
	{
		RequestInput::setTemplateBasePath(SHARED_CMS_TEMPLATE_DIR."forms/input-elements/");

		// test object with no added RequestInput properties
		$obj = new SerializedContent();
		$expected1 =
			"<input type=\"hidden\" name=\"{$obj->id->key}\" value=\"{$obj->id->value}\" />\n";
		$this->expectOutputString($expected1);
		$obj->preserveInForm();

		// test object with added RequestInput properties
		// N.B. expectOutputString() evaluates against ALL strings that have been printed to STDOUT
		$o2 = new SerializedContentUtilsChild();
		$expected2 = $expected1.
			"<input type=\"hidden\" name=\"{$o2->vc_col1->key}\" value=\"{$o2->vc_col1->value}\" />\n".
			"<input type=\"hidden\" name=\"{$o2->vc_col2->key}\" value=\"{$o2->vc_col2->value}\" />\n".
			"<input type=\"hidden\" name=\"{$o2->int_col->key}\" value=\"{$o2->int_col->value}\" />\n".
			"<input type=\"hidden\" name=\"{$o2->bool_col->key}\" value=\"{$o2->bool_col->value}\" />\n".
			"<input type=\"hidden\" name=\"{$o2->id->key}\" value=\"{$o2->id->value}\" />\n";
		$this->expectOutputString($expected2);
		$o2->preserveInForm();

		// test object with excluded properties
		$expected3 = $expected2.
			"<input type=\"hidden\" name=\"{$o2->vc_col1->key}\" value=\"{$o2->vc_col1->value}\" />\n".
			"<input type=\"hidden\" name=\"{$o2->int_col->key}\" value=\"{$o2->int_col->value}\" />\n".
			"<input type=\"hidden\" name=\"{$o2->id->key}\" value=\"{$o2->id->value}\" />\n";
		$this->expectOutputString($expected3);
		$o2->preserveInForm(array('p_vc2', 'p_bool'));

	}

    public function testCacheTemplatePath()
	{
		$this->assertEquals("/path/to/templates/child-cache-template.php", SerializedContentUtilsChild::getCacheTemplatePath());
		$this->assertEquals("", SerializedContentUtils::getCacheTemplatePath());
	}

	/**
	 * @throws ResourceNotFoundException
	 */
	public function testUpdateCacheFileWithoutContext()
	{
		$src_template_path = TEST_ASSETS_PATH . self::TEST_SOURCE_TEMPLATE;
		$dst_path = TEST_ASSETS_PATH;
		$context = array("page_title" => "My Test Title", "test_var" => "test value");
		$this->obj->updateCacheFile($context, $src_template_path, $dst_path.self::TEST_OUTPUT_TEMPLATE);
		$result = file_get_contents($dst_path.self::TEST_OUTPUT_TEMPLATE);
		$this->assertStringContainsString("<h1>My Test Title</h1>", $result);
		$this->assertStringContainsString("sample content", $result);
		$this->assertStringContainsString("inserted: test value", $result);
		$this->assertStringContainsString("final test", $result);
	}
}
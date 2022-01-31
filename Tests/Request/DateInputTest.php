<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Request\DateInput;
use Littled\Request\DateTextField;
use Littled\Request\RequestInput;
use Littled\Tests\Request\DataProvider\DateFormatTestData;
use Littled\Tests\TestExtensions\ContentValidationTestCase;
use Exception;

class DateInputTest extends ContentValidationTestCase
{
	/** @var DateInput Test DateInput object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;

	/**
	 */
	function __construct($name='', array $data=[], $data_name='')
	{
		parent::__construct($name, $data, $data_name);
		DateInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
		$this->obj = new DateInput(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY);
		$this->conn = new MySQLConnection();
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::escapeSQLProvider
	 * @throws Exception
	 */
	public function testEscapeSQL(?string $date_string, ?string $expected)
	{
		$this->conn->connectToDatabase();
		$this->obj->setInputValue($date_string);
		$this->assertEquals($expected, $this->obj->escapeSQL($this->conn->getMysqli()));
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::formatDateValueProvider()
	 * @param string|null $date_string
	 * @param string|null $format
	 * @param string|null $expected
	 * @return void
	 * @throws ContentValidationException
	 */
	function testFormatDateValueUsingArgument(?string $date_string, ?string $format, ?string $expected)
	{
		$o = new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false);
		$o->value = $date_string; /** set value here because class constructor converts passed value */
		if ('[use default]' !== $format) {
			$this->assertEquals($expected, $o->formatDateValue($format), "return value, format: $format");
		}
		else {
			$this->assertEquals($expected, $o->formatDateValue(), "return value, format: $format");
		}
		$this->assertEquals($date_string, $o->value, "internal value, format: $format");
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::formatDateValueUsingInvalidDateProvider()
	 * @param string|null $date_string
	 * @param string|null $format
	 * @param string|null $expected
	 * @return void
	 * @throws ContentValidationException
	 */
	function testFormatDateValueUsingInvalidDate(?string $date_string, ?string $format, ?string $expected)
	{
		$o = new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false);
		$o->value = $date_string;
		$this->expectExceptionMessageMatches($expected);
		$this->assertEquals($expected, $o->formatDateValue($format));
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::formatDateValueProvider()
	 * @param string|null $date_string
	 * @param string|null $format
	 * @param string|null $expected
	 * @return void
	 * @throws ContentValidationException
	 */
	function testFormatDateValueUsingProperty(?string $date_string, ?string $format, ?string $expected)
	{
		$o = new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false);
		$o->value = $date_string; /** set value here because class constructor converts passed value */
		if ('[use default]' !== $format) {
			$o->setFormat($format);
		}
		$this->assertEquals($expected, $o->formatDateValue(), "return value, format: $format");
		$this->assertEquals($date_string, $o->value, "internal value, format: $format");
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::renderTestProvider()
	 * @param DateTextField $o
	 * @param string|null $expected
	 * @param string $label
	 * @param string $css_class
	 * @return void
	 */
	function testRender(DateTextField $o, ?string $expected='', string $label='', string $css_class='')
	{
		$this->expectOutputRegex($expected);
		$o->render($label, $css_class);
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::setInputValueProvider
	 * @param string|null $date_string
	 * @param string|null $expected
	 * @return void
	 */
	public function testSetInputValue(?string $date_string, ?string $expected)
	{
		$this->obj->setInputValue($date_string);
		$this->assertEquals($expected, $this->obj->value);
	}

	function testTemplatePath()
	{
		$this->assertEquals(RequestInput::getTemplateBasePath(), DateInput::getTemplateBasePath());
		$this->assertEquals('date-text-input.php', DateInput::getInputTemplateFilename());
		$this->assertEquals('date-text-field.php', DateInput::getTemplateFilename());
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::validateMissingDateValueProvider
	 * @param string|null $date_string
	 * @param string|null $expected
	 * @return void
	 */
	public function testValidateMissingDateValue(?string $date_string, ?string $expected)
	{
		$this->obj->setAsRequired();
		if ('[use default]' !== $date_string) {
			$this->obj->value = $date_string;
		}
		$this->assertContentValidationException($this->obj);
		$this->assertMatchesRegularExpression($expected, $this->obj->error);
		$this->obj->clearValidationErrors();
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::validateValidValuesProvider
	 * @throws ContentValidationException
	 */
	public function testValidateValidValues(?string $date_string, ?string $expected)
	{
		$this->obj->required = true;
		$this->obj->value = $date_string;
		$this->obj->validate();
		$this->assertEquals($expected, $this->obj->value);
	}

	public function testValidateValueSize()
	{
		$str = str_repeat("a", $this->obj->sizeLimit + 1);
		$this->obj->value = $str;
		$this->assertContentValidationException($this->obj);
		$pattern = "/{$this->obj->label} is limited to {$this->obj->sizeLimit} characters/i";
		$this->assertMatchesRegularExpression($pattern, $this->obj->error);
		$this->obj->clearValidationErrors();
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\DateInputTestDataProvider::validateInvalidDateFormatsProvider
	 * @param string|null $date_string
	 * @param string|null $expected
	 * @return void
	 */
	public function testValidateInvalidDateFormats(?string $date_string, ?string $expected)
	{
		$this->obj->required = false;
		$this->obj->value = $date_string;
		$this->assertContentValidationException($this->obj);
		$this->assertMatchesRegularExpression($expected, $this->obj->error);
		$this->obj->clearValidationErrors();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateWhenNotRequired()
	{
		$this->obj->required = false;
		$this->obj->value = null;
		$this->obj->validate();
		$this->assertEquals('', $this->obj->error);

		$this->obj->value = '';
		$this->obj->validate();
		$this->assertEquals('', $this->obj->error);
	}
}

<?php
namespace LittledTests\Request;

use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Request\DateInput;
use Littled\Request\DateTextField;
use Littled\Request\RequestInput;
use LittledTests\DataProvider\Request\DateFormatTestData;
use LittledTests\DataProvider\Request\DateTextFieldTestData;
use LittledTests\TestExtensions\ContentValidationTestCase;
use Exception;

class DateInputTest extends ContentValidationTestCase
{
	/** @var DateInput Test DateInput object. */
	public DateInput $obj;
	/** @var MySQLConnection Test database connection. */
	public MySQLConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = new MySQLConnection();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->conn->closeDatabaseConnection();
    }

    /**
	 */
	function __construct($name='', array $data=[], $data_name='')
	{
		parent::__construct($name, $data, $data_name);
		DateInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
		$this->obj = new DateInput(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY);
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::escapeSQLProvider
	 * @throws Exception
	 */
	public function testEscapeSQL(DateFormatTestData $data)
	{
		$this->conn->connectToDatabase();
        if ($data->expected_exception_class) {
            try {
                $this->obj->setInputValue($data->date_string);
                $this->assertEquals(false, true, 'Expected exception not thrown.');
            }
            catch(Exception $e) {
                $this->assertInstanceOf($data->expected_exception_class, $e, $data->msg);
                $this->assertMatchesRegularExpression($data->expected_exception, $e->getMessage(), $data->msg);
            }
            $this->assertEquals(false, true, 'Expected exception $data->expected_exception_class not thrown. '.$data->msg);
        }
        else {
            $this->obj->setInputValue($data->date_string);
            if ($data->date_string===null) {
                $this->obj->value = null;
            }
            $this->assertEquals($data->expected, $this->obj->escapeSQL($this->conn->getMysqli()), $data->msg);
        }
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::formatDateValueProvider()
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
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::formatDateValueUsingInvalidDateProvider()
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
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::formatDateValueProvider()
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

    function testGetPreparedStatementTypeIdentifier()
    {
        $this->assertEquals('s', DateInput::getPreparedStatementTypeIdentifier());
    }

    /**
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::renderTestProvider()
	 * @param DateTextFieldTestData $data
	 * @return void
	 */
	function testRender(DateTextFieldTestData $data)
	{
        ob_start();
		$data->obj->render($data->label_override, $data->css_override);
        $markup = ob_get_contents();
        ob_end_clean();
        $this->assertMatchesRegularExpression($data->expected, $markup, $data->msg);
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::setInputValueProvider
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
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::validateMissingDateValueProvider
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
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::validateValidValuesProvider
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
		$str = str_repeat("a", $this->obj->size_limit + 1);
		$this->obj->value = $str;
		$this->assertContentValidationException($this->obj);
		$pattern = "/{$this->obj->label} is limited to {$this->obj->size_limit} characters/i";
		$this->assertMatchesRegularExpression($pattern, $this->obj->error);
		$this->obj->clearValidationErrors();
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Request\DateInputTestDataProvider::validateInvalidDateFormatsProvider
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

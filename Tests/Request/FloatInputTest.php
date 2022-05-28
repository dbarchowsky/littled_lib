<?php
namespace Littled\Tests\Request;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Request\FloatInput;
use Littled\Exception\ContentValidationException;
use Littled\Request\RequestInput;
use Littled\Tests\Request\DataProvider\FloatInputTestData;
use Littled\Tests\TestExtensions\ContentValidationTestCase;
use mysqli;

class FloatInputTest extends ContentValidationTestCase
{
    /** @var MySQLConnection */
    public $conn;
    /** @var mysqli */
    public $mysqli;

	/**
	 * @throws ConnectionException
	 * @throws ConfigurationUndefinedException
	 */
	protected function setUp(): void
    {
        parent::setUp();
        $this->conn = new MySQLConnection();
        $this->conn->connectToDatabase();
        $this->mysqli = $this->conn->getMysqli();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->conn->closeDatabaseConnection();
    }

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\FloatInputTestDataProvider::collectRequestDataTestProvider()
	 * @param $expected
	 * @param $value
	 * @return void
	 */
	function testCollectRequestData($expected, $value)
	{
		$obj = new FloatInput(FloatInputTestData::DEFAULT_LABEL, FloatInputTestData::DEFAULT_KEY);
		$obj->collectRequestData(array(FloatInputTestData::DEFAULT_KEY => $value));
		$this->assertEquals($expected, $obj->value);
	}

	public function testConstructor()
	{
		$obj = new FloatInput("Label", "key", false, 0);
		$this->assertEquals(0, $obj->value);
	}

	public function testConstructorUsingStringValue()
	{
		$obj = new FloatInput("Label", "key", false, "string value");
		$this->assertEquals(null, $obj->value);
	}

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\FloatInputTestDataProvider::escapeSQLTestProvider()
     * @param FloatInputTestData $data
     * @return void
     */
    public function testEscapeSQL(FloatInputTestData $data)
	{
        if (null===$data->expected) {
            $this->assertNull($data->obj->value);
        }
        else {
            $this->assertEquals($data->expected, $data->obj->escapeSQL($this->mysqli));
        }
	}

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\FloatInputTestDataProvider::renderTestProvider()
     * @param FloatInputTestData $data
     * @return void
     */
    function testRender(FloatInputTestData $data)
    {
        ob_start();
        $data->obj->render($data->label_override, $data->css_override);
        $markup = ob_get_contents();
        ob_end_clean();
        $this->assertMatchesRegularExpression($data->expected_regex, $markup, $data->msg);
    }

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\FloatInputTestDataProvider::renderInputTestProvider()
     * @param FloatInputTestData $data
     * @return void
     */
    function testRenderInput(FloatInputTestData $data)
    {
        ob_start();
        $data->obj->renderInput($data->label_override, $data->css_override);
        $markup = ob_get_contents();
        ob_end_clean();
        $this->assertMatchesRegularExpression($data->expected_regex, $markup, $data->msg);
    }

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\FloatInputTestDataProvider::setInputValueTestProvider()
     * @param FloatInputTestData $data
     * @return void
     */
    public function testSetInputValue(FloatInputTestData $data)
    {
        if (null === $data->expected) {
            $this->assertNull($data->obj->value);
        }
        else {
            $this->assertEquals($data->expected, $data->obj->value);
        }
    }

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateValidValues()
	{
		$o = new FloatInput("Test", "test");

		/* not required, default value (null) */
		$o->required = false;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* not required, empty string value */
		$o->required = false;
		$o->value = '';
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* not required, valid integer value of 1 */
		$o->required = false;
		$o->value = 1;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* not required, valid integer value */
		$o->required = false;
		$o->value = 765;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, valid integer value of 1 */
		$o->required = true;
		$o->value = 1;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, valid integer value of 1 */
		$o->required = true;
		$o->value = 0;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, valid integer value */
		$o->required = true;
		$o->value = 5248;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* not required, null value */
		$o->required = false;
		$o->value = null;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, integer string */
		$o->required = true;
		$o->value = '1';
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, integer string */
		$o->required = true;
		$o->value = '0';
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, integer string */
		$o->required = true;
		$o->value = '8356';
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, float value */
		$o->required = true;
		$o->value = 99.06;
		$o->validate();
		$this->assertFalse($o->has_errors);

		/* required, float string */
		$o->required = true;
		$o->value = '99.06';
		$o->validate();
		$this->assertFalse($o->has_errors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredDefaultValue()
	{
		$o = new FloatInput(FloatInputTestData::DEFAULT_LABEL, FloatInputTestData::DEFAULT_KEY, true);
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredEmptyStringValue()
	{
		$o = new FloatInput(FloatInputTestData::DEFAULT_LABEL, FloatInputTestData::DEFAULT_KEY, true);
		$o->value = '';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateRequiredStringValue()
	{
		$o = new FloatInput(FloatInputTestData::DEFAULT_LABEL, FloatInputTestData::DEFAULT_KEY, true);
		$o->value = 'foo';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateNotRequiredStringValue()
	{
		$o = new FloatInput(FloatInputTestData::DEFAULT_LABEL, FloatInputTestData::DEFAULT_KEY, false);
		$o->value = 'foo';
		$this->expectException(ContentValidationException::class);
		$o->validate();
	}
}

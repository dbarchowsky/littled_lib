<?php
namespace LittledTests\Request;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Request\FloatInput;
use Littled\Exception\ContentValidationException;
use Littled\Request\RequestInput;
use LittledTests\DataProvider\Request\FloatInputTestData;
use LittledTests\TestExtensions\ContentValidationTestCase;
use mysqli;

class FloatInputTest extends ContentValidationTestCase
{
    public static MySQLConnection $conn;
    public static mysqli $mysqli;

    /**
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$conn = new MySQLConnection();
        static::$conn->connectToDatabase();
        static::$mysqli = static::$conn->getMysqli();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$conn->closeDatabaseConnection();
    }

	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Request\FloatInputTestDataProvider::collectRequestDataTestProvider()
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
     * @dataProvider \LittledTests\DataProvider\Request\FloatInputTestDataProvider::escapeSQLTestProvider()
     * @param FloatInputTestData $data
     * @return void
     */
    public function testEscapeSQL(FloatInputTestData $data)
	{
        $this->assertSame($data->expected, $data->obj->escapeSQL(static::$mysqli));
	}

    function testGetPreparedStatementTypeIdentifier()
    {
        $this->assertEquals('d', FloatInput::getPreparedStatementTypeIdentifier());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\FloatInputTestDataProvider::hasDataTestProvider()
     * @param bool $expected
     * @param $value
     * @return void
     */
    public function testHasData(bool $expected, $value)
    {
        $o = new FloatInput('Label','key');
        $o->value = $value;
        self::assertEquals($expected, $o->hasData());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\FloatInputTestDataProvider::renderTestProvider()
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
     * @dataProvider \LittledTests\DataProvider\Request\FloatInputTestDataProvider::renderInputTestProvider()
     * @param FloatInputTestData $data
     * @return void
     */
    function testRenderInput(FloatInputTestData $data)
    {
        ob_start();
        $data->obj->renderInput($data->label_override);
        $markup = ob_get_contents();
        ob_end_clean();
        $this->assertMatchesRegularExpression($data->expected_regex, $markup, $data->msg);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\FloatInputTestDataProvider::setInputValueTestProvider()
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
     * @dataProvider \LittledTests\DataProvider\Request\FloatInputTestDataProvider::validateTestProvider()
     * @param string $expected_exception
     * @param $value
     * @param bool $required
	 */
    public function testValidate(string $expected_exception, $value, bool $required)
    {
        $o = new FloatInput('Label', 'key', $required);
        $this->_testValidate($expected_exception, $value, $o);
    }
}

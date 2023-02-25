<?php
namespace Littled\Tests\Request;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Tests\DataProvider\Request\BooleanInputTestData;
use Littled\Tests\TestExtensions\ContentValidationTestCase;
use mysqli;
use Littled\Request\RequestInput;
use Littled\Request\BooleanInput;
use Littled\Exception\ContentValidationException;

class BooleanInputTest extends ContentValidationTestCase
{
    public BooleanInput $o;
	public MySQLConnection $conn;
	public mysqli $mysqli;

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

    /**
	 * @param string|null $name
	 * @param array $data
	 * @param mixed $dataName
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->o = new BooleanInput('Test Input Label', 'testKey');
		RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Request\BooleanInputTestDataProvider::escapeSQLProvider()
	 * @param BooleanInputTestData $data
	 * @return void
	 */
	public function testEscapeSQL(BooleanInputTestData $data)
	{
		if ('[use default]' !== $data->obj->value) {
			$data->obj->value = $data->value;
		}
        if ($data->expected===null) {
            $this->assertNull($data->obj->escapeSQL($this->mysqli));
        }
        else {
            $this->assertEquals($data->expected, $data->obj->escapeSQL($this->mysqli));
        }
	}

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Request\BooleanInputTestDataProvider::formatValueMarkupProvider()
	 * @param BooleanInputTestData $data
	 * @return void
	 */
    public function testFormatValueMarkup(BooleanInputTestData $data)
    {
	    if ('[use default]' !== $data->obj->value) {
		    $data->obj->value = $data->value;
	    }
	    $this->assertEquals($data->expected, $data->obj->formatValueMarkup());
    }

	public function testIsEmpty()
	{
		$o = new BooleanInput('Test', 'test');

		$o->value = true;
		self::assertTrue($o->isEmpty());

		$o->value = false;
		self::assertTrue($o->isEmpty());
	}

    function testGetPreparedStatementTypeIdentifier()
    {
        $this->assertEquals('i', BooleanInput::getPreparedStatementTypeIdentifier());
    }

    /**
	 * @dataProvider \Littled\Tests\DataProvider\Request\BooleanInputTestDataProvider::saveInFormProvider()
	 * @param BooleanInputTestData $data
	 * @return void
	 */
	public function testSaveInForm(BooleanInputTestData $data)
	{
		$o = new BooleanInput(BooleanInputTestData::DEFAULT_LABEL, BooleanInputTestData::DEFAULT_KEY);
		$o->value = $data->value;
		$this->expectOutputRegex($data->expected_regex);
		$o->saveInForm();
	}

    /**
     * @throws ContentValidationException
     */
    public function testSetValue()
    {
        // test that the unprocessed value will throw a validation error due to the fact that it is in string format
        $this->o->value = '0';
        $this->assertContentValidationException($this->o);
        $this->o->clearValidationErrors();

        // setInputValue() should convert the string into a boolean value
        $this->o->setInputValue($this->o->value);
        $this->o->validate();
        $this->assertFalse($this->o->has_errors);
    }


	/**
	 * @dataProvider \Littled\Tests\DataProvider\Request\BooleanInputTestDataProvider::setValidateProvider()
	 * @param BooleanInputTestData $data
	 * @return void
	 * @throws ContentValidationException
	 */
	public function testValidate(BooleanInputTestData $data)
	{
		$data->obj->value = $data->value;
		if (false===$data->expected) {
			$data->obj->validate();
			$this->assertFalse($data->obj->has_errors);
		}
		else {
			$this->assertContentValidationException($data->obj);
		}
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnUnset()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->validate();
		$this->assertTrue($o->has_errors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnNull()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->value = null;
		$o->validate();
		$this->assertTrue($o->has_errors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnBadValueWhenRequired()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = true;
		$o->value = 'true';
		$o->validate();
		$this->assertTrue($o->has_errors);
	}

	/**
	 * @throws ContentValidationException
	 */
	public function testValidateExceptionOnBadValueWhenNotRequired()
	{
		$this->expectException(ContentValidationException::class);
		$o = new BooleanInput("Test", "test");
		$o->required = false;
		$o->value = 'true';
		$o->validate();
		$this->assertTrue($o->has_errors);
	}

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Request\BooleanInputTestDataProvider::setInputValueProvider()
	 * @param BooleanInputTestData $data
	 * @return void
	 */
	public function testSetInputValue(BooleanInputTestData $data)
	{
		$o = new BooleanInput(BooleanInputTestData::DEFAULT_LABEL, BooleanInputTestData::DEFAULT_KEY);
		if ('[use default]' !== $data->value) {
			$o->setInputValue($data->value);
		}
		if (true === $data->expected) {
			$this->assertTrue($o->value);
		}
		elseif(null === $data->expected) {
			$this->assertNull($o->value);
		}
		else {
			$this->assertFalse($o->value);
		}
	}
}

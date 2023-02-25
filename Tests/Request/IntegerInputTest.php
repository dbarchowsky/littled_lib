<?php
namespace Littled\Tests\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Littled\Request\IntegerInput;
use Littled\Exception\ContentValidationException;
use Littled\Request\RequestInput;
use Littled\Tests\DataProvider\Request\IntegerInputTestData;
use PHPUnit\Framework\TestCase;
use mysqli;

class IntegerInputTest extends TestCase
{
    public IntegerInput $obj;
    /** @var bool */
    const MAKE_HTTP_REQUEST = false;

	/** @var string Path to test harness page that collects integer post data using IntegerInput object. */
	const TEST_HARNESS_COLLECT_PATH = 'Tests/collect/integer';
	/** @var string Path to test harness page that validates integer post data using IntegerInput object. */
	const TEST_HARNESS_VALIDATE_PATH = 'Tests/validate/integer';
	/** @var string Name of variable used to pass form data to test harness page. */
	const TEST_HARNESS_VARIABLE_NAME = 'var';

    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->obj = new IntegerInput('Test Input', 'test_key');
    }

    protected function expectValidValue(IntegerInput $o)
	{
		try {
			$o->validate();
			$this->assertEquals('Validated input value.', 'Validated input value.');
			$this->assertFalse($o->has_errors);
		}
		catch (ContentValidationException $ex) {
			$this->assertEquals('', 'Caught content validate exception: '.$ex->getMessage());
		}
	}

	protected function expectInvalidValue(IntegerInput $o, string $err_msg)
	{
		try {
			$o->validate();
			$this->assertEquals('', 'Content validation exception not thrown.');
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals($err_msg, $ex->getMessage());
		}
	}

	/**
	 * @param mixed $value Value to pass to the test harness page.
	 * @return ?bool Test harness response as json object.
     * @throws GuzzleException
     */
	protected function sendCollectTestHarnessRequest($value): ?bool
	{
		$client = new Client(['base_uri' => TEST_HARNESS_BASE_URI]);
		$response = $client->post(self::TEST_HARNESS_COLLECT_PATH, [
			'form_params' => [
				self::TEST_HARNESS_VARIABLE_NAME => $value
			]
		]);
		return(json_decode($response->getBody()->getContents(), true));
	}

	/**
	 * @param mixed $value Value to pass to the test harness page.
	 * @return ?bool Test harness response as json object.
     * @throws GuzzleException
     */
	protected function sendValidateTestHarnessRequest($value): ?bool
	{
		$client = new Client(['base_uri' => TEST_HARNESS_BASE_URI]);
		$response = $client->post(self::TEST_HARNESS_VALIDATE_PATH, [
			'form_params' => [
				self::TEST_HARNESS_VARIABLE_NAME => $value
			]
		]);
		return(json_decode($response->getBody()->getContents(), true));
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\IntegerInputTestDataProvider::collectRequestDataTestProvider()
     * @param $expected
     * @param $value
     * @return void
     */
    function testCollectRequestData($expected, $value)
    {
        $obj = new IntegerInput(IntegerInputTestData::DEFAULT_LABEL, IntegerInputTestData::DEFAULT_KEY);
        $obj->collectRequestData(array(IntegerInputTestData::DEFAULT_KEY => $value));
        $this->assertEquals($expected, $obj->value);
    }

	public function testConstructor()
	{
		$obj = new IntegerInput("Label", "key", false, 0);
		$this->assertEquals(0, $obj->value);
	}

	public function testConstructorUsingStringValue()
	{
		$obj = new IntegerInput("Label", "key", false, "string value");
		$this->assertEquals(null, $obj->value);
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingNull()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest(null);
            $this->assertEquals("Integer variable was collected: \"\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingValidIntegerValue()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest(23);
            $this->assertEquals("Integer variable was collected: \"23\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingNegativeIntegerValue()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest(-62);
            $this->assertEquals("Integer variable was collected: \"-62\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingOne()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest(1);
            $this->assertEquals("Integer variable was collected: \"1\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingZero()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest(0);
            $this->assertEquals("Integer variable was collected: \"0\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingTrue()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest(true);
            $this->assertEquals("Integer variable was collected: \"1\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingFalse()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest(false);
            $this->assertEquals("Integer variable was collected: \"0\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingString()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest('foo');
            $this->assertEquals("Integer variable was collected: \"\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testCollectPostDataUsingFloat()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendCollectTestHarnessRequest('23.75');
            $this->assertEquals("Integer variable was collected: \"\".", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\IntegerInputTestDataProvider::escapeSQLTestProvider()
     * @param $expected
     * @param $value
     * @param mysqli $mysqli
     * @param string $msg
     * @return void
     */
    public function testEscapeSQL($expected, $value, mysqli $mysqli, string $msg='')
    {
        $o = new IntegerInput(IntegerInputTestData::DEFAULT_LABEL, IntegerInputTestData::DEFAULT_KEY);
        $o->setInputValue($value);
        $this->assertSame($expected, $o->escapeSQL($mysqli), $msg);
    }

    function testGetPreparedStatementTypeIdentifier()
    {
        $this->assertEquals('i', IntegerInput::getPreparedStatementTypeIdentifier());
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\IntegerInputTestDataProvider::renderTestProvider()
     * @param IntegerInputTestData $data
     * @return void
     */
    function testRender(IntegerInputTestData $data)
    {
        ob_start();
        $data->obj->render($data->label_override, $data->css_override);
        $markup = ob_get_contents();
        ob_end_clean();
        $this->assertMatchesRegularExpression($data->expected_regex, $markup, $data->msg);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\IntegerInputTestDataProvider::renderInputTestProvider()
     * @param IntegerInputTestData $data
     * @return void
     */
    function testRenderInput(IntegerInputTestData $data)
    {
        ob_start();
        $data->obj->renderInput($data->label_override);
        $markup = ob_get_contents();
        ob_end_clean();
        $this->assertMatchesRegularExpression($data->expected_regex, $markup, $data->msg);
    }

    public function testSafeValue()
    {
        $this->obj->value = null;
        $this->assertEquals('', $this->obj->safeValue());

        $this->obj->value = 0;
        $this->assertEquals('0', $this->obj->safeValue());

        $this->obj->value = 1;
        $this->assertEquals('1', $this->obj->safeValue());

        $this->obj->value = 16752;
        $this->assertEquals('16752', $this->obj->safeValue());

        $this->obj->value = '<script>alert("hello!")</script>';
        $this->assertEquals('', $this->obj->safeValue());
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\IntegerInputTestDataProvider::setInputValueTestProvider()
     * @param ?int $expected
     * @param mixed $value
     * @param string $msg
     * @return void
     */
    public function testSetInputValue(?int $expected, $value, string $msg='')
    {
        $o = new IntegerInput(IntegerInputTestData::DEFAULT_LABEL, IntegerInputTestData::DEFAULT_KEY);
        if ('[use default]' !== $value) {
            $o->setInputValue($value);
        }
        $this->assertEquals($expected, $o->value, $msg);
    }

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingNull()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest(null);
            $this->assertEquals(0, $json['data']['status']);
            $this->assertNull($json['data']['collected_value']);
            $this->assertEquals("Test variable is required.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingValidIntegerValue()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest(23);
            $this->assertEquals(1, $json['data']['status']);
            $this->assertEquals(23, $json['data']['collected_value']);
            $this->assertEquals("Validate integer ok.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingNegativeIntegerValue()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest(-62);
            $this->assertEquals(1, $json['data']['status']);
            $this->assertEquals(-62, $json['data']['collected_value']);
            $this->assertEquals("Validate integer ok.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingOne()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest(1);
            $this->assertEquals(1, $json['data']['status']);
            $this->assertEquals(1, $json['data']['collected_value']);
            $this->assertEquals("Validate integer ok.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingZero()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest(0);
            $this->assertEquals(1, $json['data']['status']);
            $this->assertEquals(0, $json['data']['collected_value']);
            $this->assertEquals("Validate integer ok.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingTrue()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest(true);
            $this->assertEquals(1, $json['data']['status']);
            $this->assertEquals(1, $json['data']['collected_value']);
            $this->assertEquals("Validate integer ok.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingFalse()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest(false);
            $this->assertEquals(1, $json['data']['status']);
            $this->assertEquals(0, $json['data']['collected_value']);
            $this->assertEquals("Validate integer ok.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingString()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest('foo');
            $this->assertEquals(0, $json['data']['status']);
            $this->assertNull($json['data']['collected_value']);
            $this->assertEquals("Test variable is required.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

    /**
     * @throws GuzzleException
     */
    public function testValidatePostDataUsingFloat()
	{
        if (self::MAKE_HTTP_REQUEST) {
            $json = $this->sendValidateTestHarnessRequest('23.75');
            $this->assertEquals(0, $json['data']['status']);
            $this->assertNull($json['data']['collected_value']);
            $this->assertEquals("Test variable is required.", $json['data']['result']);
        }
        else {
            $this->assertEquals(true, 1);
        }
	}

	public function testValidateValidValues()
	{
		$o = new IntegerInput("Test", "test");

		/* not required, default value (null) */
		$o->required = false;
		$this->expectValidValue($o);

		/* not required, empty string value */
		$o->required = false;
		$o->value = '';
		$this->expectValidValue($o);

		/* not required, valid integer value of 1 */
		$o->required = false;
		$o->value = 1;
		$this->expectValidValue($o);

		/* not required, valid integer value */
		$o->required = false;
		$o->value = 765;
		$this->expectValidValue($o);

		/* required, valid integer value of 1 */
		$o->required = true;
		$o->value = 1;
		$this->expectValidValue($o);

		/* required, valid integer value of 0 */
		$o->required = true;
		$o->value = 0;
		$this->expectValidValue($o);

		/* required, valid integer value */
		$o->required = true;
		$o->value = 5248;
		$this->expectValidValue($o);

		/* not required, null value */
		$o->required = false;
		$o->value = null;
		$this->expectValidValue($o);

		/* required, integer string */
		$o->required = true;
		$o->value = '1';
		$this->expectValidValue($o);

		/* required, integer string */
		$o->required = true;
		$o->value = '0';
		$this->expectValidValue($o);

		/* required, integer string */
		$o->required = true;
		$o->value = '8356';
		$this->expectValidValue($o);
	}

	public function testValidateRequiredDefaultValue()
	{
		$o = new IntegerInput('test label', 'ptest', true);
		$this->expectInvalidValue($o, 'Test label is required.');
	}

	public function testValidateRequiredEmptyStringValue()
	{
		$o = new IntegerInput('test label', 'ptest', true);
		$o->value = '';
		$this->expectInvalidValue($o, 'Test label is required.');
	}

	public function testValidateRequiredStringValue()
	{
		$o = new IntegerInput('test label', 'ptest', true);
		$o->value = 'foo';
		$this->expectInvalidValue($o, 'Test label is in unrecognized format.');
	}

	public function testValidateNotRequiredStringValue()
	{
		$o = new IntegerInput('test label', 'ptest', false);
		$o->value = 'foo';
		$this->expectInvalidValue($o, 'Test label is in unrecognized format.');
	}
}

<?php
namespace Littled\Tests\Validation;

use Exception;
use Littled\App\LittledGlobals;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidRequestException;
use Littled\Exception\InvalidValueException;
use Littled\Tests\TestHarness\Validation\ValidationTestHarness;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;
use stdClass;


class ValidationTest extends TestCase
{
    protected const AJAX_INPUT_SOURCE = APP_BASE_DIR.'Tests/DataProvider/Validation/test-ajax-data.dat';

    public function testCheckForCookieConsent()
    {
        if (isset($_COOKIE)) {
            unset($_COOKIE[LittledGlobals::COOKIE_CONSENT_KEY]);
        }
        if (isset($_SESSION)) {
            unset($_SESSION[LittledGlobals::COOKIE_CONSENT_KEY]);
        }
        $this->assertFalse(Validation::checkForCookieConsent());

        $_SESSION[LittledGlobals::COOKIE_CONSENT_KEY] = false;
        $this->assertFalse(Validation::checkForCookieConsent());

        $_SESSION[LittledGlobals::COOKIE_CONSENT_KEY] = true;
        $this->assertTrue(Validation::checkForCookieConsent());

        $_SESSION[LittledGlobals::COOKIE_CONSENT_KEY] = '1';
        $this->assertFalse(Validation::checkForCookieConsent());

        $_SESSION[LittledGlobals::COOKIE_CONSENT_KEY] = '4';
        $this->assertFalse(Validation::checkForCookieConsent());

        unset($_SESSION[LittledGlobals::COOKIE_CONSENT_KEY]);

        $_COOKIE[LittledGlobals::COOKIE_CONSENT_KEY] = false;
        $this->assertFalse(Validation::checkForCookieConsent());

        $_COOKIE[LittledGlobals::COOKIE_CONSENT_KEY] = true;
        $this->assertTrue(Validation::checkForCookieConsent());

        $_COOKIE[LittledGlobals::COOKIE_CONSENT_KEY] = '1';
        $this->assertTrue(Validation::checkForCookieConsent());

        $_COOKIE[LittledGlobals::COOKIE_CONSENT_KEY] = '4';
        $this->assertTrue(Validation::checkForCookieConsent());

        unset($_COOKIE[LittledGlobals::COOKIE_CONSENT_KEY]);
        $this->assertFalse(Validation::checkForCookieConsent());
    }

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::collectIntegerArrayRequestVarTestProvider()
	 * @param array $expected
	 * @param string $key
	 * @param mixed $values
	 * @return void
	 */
	public function testCollectIntegerArrayRequestVar(array $expected, string $key, $values)
	{
		$_POST[$key] = $values;
		$result = Validation::collectIntegerArrayRequestVar($key);
		$this->assertCount(count($expected), $result, "key: $key");
		for($i=0; $i<count($expected); $i++) {
			$this->assertEquals($expected[$i], $result[$i], "key: $key");
		}
        unset($_POST[$key]);
	}

	/**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::parseIntegerTestProvider()
     * @param $expected
     * @param $value
     * @param string $msg
     * @return void
     */
    public function testCollectIntegerRequestVar($expected, $value, string $msg='')
    {
        $this->assertEquals($expected, Validation::collectIntegerRequestVar('testKey', null, array('testKey' => $value)), $msg);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::parseIntegerTestProvider()
     * @param $expected
     * @param $value
     * @param string $msg
     * @return void
     */
    public function testCollectIntegerRequestVar_PostData($expected, $value, string $msg='')
    {
        $_POST['testKey'] = $value;
        $this->assertEquals($expected, Validation::collectIntegerRequestVar('testKey'), $msg);
        unset($_POST['testKey']);
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::getClientIPTestProvider()
     * @param bool $expected
     * @param string $ip
     * @param string $key
     * @param string $msg
     * @param string $ip2
     * @param string $key2
     * @return void
     */
    public function testGetClientIP(bool $expected, string $ip, string $key, string $msg='', string $ip2='', string $key2='')
    {
        ValidationTest::clearServerIPValues();
        $_SERVER[$key] = $ip;
        if ($ip2 && $key2) {
            $_SERVER[$key2] = $ip2;
        }
        if ($expected) {
            $this->assertEquals($ip, ValidationTestHarness::publicGetClientIP());
        }
        else {
            $this->assertEquals('', ValidationTestHarness::publicGetClientIP());
        }
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::getClientLocationTestProvider()
     * @param ?array $expected
     * @param string $ip
     * @param string $key
     * @param string $msg
     * @param string $ip2
     * @param string $key2
     * @return void
     * @throws InvalidValueException
     */
    public function testGetClientLocation(?array $expected, string $ip, string $key, string $msg='', string $ip2='', string $key2='')
    {
        $test_ip = ValidationTest::setUpIPTest($ip, $key, $ip2, $key2);
        if (is_array($expected) && count($expected) > 0) {
            $location_data = Validation::getClientLocation($test_ip);
            foreach($expected as $key => $value) {
                if ($key !== 'is_eu') {
                    $this->assertEquals($value, $location_data[$key], $msg);
                }
            }
        }
        else {
            // testing invalid ip value
            $this->expectException(InvalidValueException::class);
            Validation::getClientLocation($test_ip);
        }
    }

    function testGetAjaxClientRequestData()
    {
        $expected = array("key1" => "value1", "keyTwo" => "value two", "jsonKey" => "json value");
        Validation::setAjaxInputStream(self::AJAX_INPUT_SOURCE);
        $data = Validation::getAjaxClientRequestData();
        $this->assertEquals($expected, $data);

        // restore state
        Validation::setAjaxInputStream('php://input');
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::getDefaultInputSourceTestProvider()
     * @param array $expected
     * @param array $get_data
     * @param array $post_data
     * @param string $input_stream
     * @param array $ignore_keys
     * @return void
     */
    function testGetDefaultInputSource(
        array $expected,
        array $get_data=[],
        array $post_data=[],
        string $input_stream='',
        array $ignore_keys=[] )
    {
        $_GET = $get_data;
        $_POST = $post_data;
        if ($input_stream) {
            ValidationTestHarness::setAjaxInputStream(self::AJAX_INPUT_SOURCE);
        }

        $this->assertEquals($expected, ValidationTestHarness::publicGetDefaultInputSource($ignore_keys));

        // restore original state
        $_GET = $_POST = [];
        ValidationTestHarness::setAjaxInputStream('php://input');
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::getClientLocationTestProvider()
     * @param ?array $expected
     * @param string $ip
     * @param string $key
     * @param string $msg
     * @param string $ip2
     * @param string $key2
     * @return void
     * @throws InvalidRequestException
     * @throws InvalidValueException
     */
    public function testIsEUClient(?array $expected, string $ip, string $key, string $msg='', string $ip2='', string $key2='')
    {
        $test_ip = ValidationTest::setUpIPTest($ip, $key, $ip2, $key2);
        if (is_array($expected) && count($expected) > 0) {
            $this->assertEquals($expected['is_eu'], Validation::isEUClient($test_ip), $msg);
        }
        else {
            // testing invalid ip value
            $this->expectException(InvalidValueException::class);
            Validation::isEUClient($test_ip);
        }
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::isIntegerTestProvider()
     * @param $expected
     * @param $value
     * @return void
     */
    public function testIsInteger($expected, $value)
    {
        $this->assertEquals($expected, Validation::isInteger($value));
    }

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::isStringWithContentTestProvider()
	 * @param bool $expected
	 * @param mixed $value
	 * @return void
	 */
	public function testIsStringWithContent(bool $expected, $value)
	{
		if ($expected) {
			$this->assertTrue(Validation::isStringWithContent($value));
		}
		else {
			$this->assertFalse(Validation::isStringWithContent($value));
		}
	}

    function testParseInput_PostData()
    {
        $_POST['key1'] = 'value1';
        $_POST['key2'] = 'value two';

        $this->assertEquals('value1', ValidationTestHarness::parseInput_Public(FILTER_UNSAFE_RAW, 'key1'));
        $this->assertEquals('value two', ValidationTestHarness::parseInput_Public(FILTER_UNSAFE_RAW, 'key2'));
        $this->assertEquals(null, ValidationTestHarness::parseInput_Public(FILTER_UNSAFE_RAW, 'NonexistentKey'));
        unset($_POST['key1']);
        unset($_POST['key2']);
    }

    function testParseInput_AjaxData()
    {
        Validation::setAjaxInputStream(self::AJAX_INPUT_SOURCE);
        $this->assertEquals('value1', ValidationTestHarness::parseInput_Public(FILTER_UNSAFE_RAW, 'key1'));
        $this->assertEquals('value two', ValidationTestHarness::parseInput_Public(FILTER_UNSAFE_RAW, 'keyTwo'));
        $this->assertEquals(null, ValidationTestHarness::parseInput_Public(FILTER_UNSAFE_RAW, 'NonexistentKey'));
        Validation::setAjaxInputStream('php://input');
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::parseNumericTestProvider()
     * @param $expected
     * @param $value
     * @return void
     */
	public function testParseNumeric($expected, $value)
	{
        $this->assertEquals($expected, Validation::parseNumeric($value));
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::parseIntegerTestProvider()
     * @param $expected
     * @param $value
     * @param string $msg
     * @return void
     */
	public function testParseInteger($expected, $value, string $msg='')
	{
        $this->assertEquals($expected, Validation::parseInteger($value), $msg);
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::stripTagsTestProvider()
     * @return void
     * @throws Exception
     */
    function testStripTags(string $expected, string $key, string $src, array $whitelist_tags, string $collection='', string $msg='')
    {
        $data = null;
        switch ($collection) {
            case 'POST':
                $_POST[$key] = $src;
                break;
            case 'REQUEST':
                $_REQUEST[$key] = $src;
                break;
            default:
                $data = array($key => $src);
        }
        self::assertMatchesRegularExpression($expected, Validation::stripTags($key, $whitelist_tags, null, $data), $msg);
    }

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::validateCSRFTestProvider()
	 * @param string $description
	 * @param bool $expected
	 * @param array $post_data
	 * @param array $session_data
	 * @param stdClass|null $user_data
	 * @param array|null $header_data
	 * @return void
	 */
	function testValidateCSRF(string $description, bool $expected, array $post_data, array $session_data, ?stdClass $user_data=null, ?array $header_data=null)
	{
		$_POST = $post_data;
		$_SESSION = $session_data;
		if (is_array($header_data)) {
			foreach ($header_data as $key => $value) {
				$_SERVER[$key] = $value;
			}
		}
		if ($expected) {
			$this->assertTrue(Validation::validateCSRF($user_data), $description);
		}
		else {
			$this->assertFalse(Validation::validateCSRF($user_data), $description);
		}
		if (is_array($header_data)) {
			foreach (array_keys($header_data) as $key) {
				unset($_SERVER[$key]);
			}
		}
        $_POST = [];
        $_SESSION = [];
	}

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Validation\ValidationTestDataProvider::validateDateStringTestProvider()
	 * @param string $expected
	 * @param string $expected_exception
	 * @param string $date_string
	 * @param string $format
	 * @return void
	 * @throws ContentValidationException
	 */
	public function testValidateDateString(string $expected, string $expected_exception, string $date_string, string $format)
	{
		if ($expected_exception) {
			$this->expectException($expected_exception);
		}
		$d = Validation::validateDateString($date_string);
		$this->assertEquals($expected, $d->format('Y-m-d'), "$format format");
	}

    /**
     * Clears any residual IP data stored in Server variables.
     * @return void
     */
    protected static function clearServerIPValues()
    {
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '';
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        }
        if (!empty($_SERVER['HTTP_CLIENT_ID'])) {
            $_SERVER['HTTP_CLIENT_ID'] = '';
        }
    }

    protected static function setUpIPTest($ip, $key, $ip2, $key2): string
    {
        ValidationTest::clearServerIPValues();
        $test_ip = '';
        if (in_array($key, ['HTTP_CLIENT_ID','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'])) {
            // Set a server variable to mock the desired header value.
            $_SERVER[$key] = $ip;
        }
        else {
            // Test the ip address directly when $key doesn't match a valid header.
            $test_ip = $ip;
        }
        if ($ip2 && $key2) {
            // Secondary header value to the precedence of header variables used to extract location.
            $_SERVER[$key2] = $ip2;
        }
        return $test_ip;
    }
}
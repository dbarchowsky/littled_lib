<?php
namespace Littled\Tests\Validation;

use Littled\App\LittledGlobals;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidRequestException;
use Littled\Exception\InvalidValueException;
use Littled\Tests\Request\DataProvider\IntegerInputTestData;
use Littled\Tests\Validation\DataProvider\ValidationTestHarness;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;
use stdClass;


class ValidationTest extends TestCase
{
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
	 * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::collectIntegerArrayRequestVarTestProvider()
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
	}

	/**
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::parseIntegerTestProvider()
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
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::getClientIPTestProvider()
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
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::getClientLocationTestProvider()
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

    /**
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::getClientLocationTestProvider()
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
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::isIntegerTestProvider()
     * @param $expected
     * @param $value
     * @return void
     */
    public function testIsInteger($expected, $value)
    {
        $this->assertEquals($expected, Validation::isInteger($value));
    }

	/**
	 * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::isStringWithContentTestProvider()
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

	/**
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::parseNumericTestProvider()
     * @param $expected
     * @param $value
     * @return void
     */
	public function testParseNumeric($expected, $value)
	{
        $this->assertEquals($expected, Validation::parseNumeric($value));
	}

    /**
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::parseIntegerTestProvider()
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
	 * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::validateCSRFTestProvider()
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
	}

	/**
	 * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::validateDateStringTestProvider()
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
<?php
namespace Littled\Tests\Validation;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Exception\ContentValidationException;
use Littled\Tests\Request\DataProvider\IntegerInputTestData;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;
use stdClass;


class ValidationTest extends TestCase
{
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
	 * @param bool $expected
	 * @param array $post_data
	 * @param array $session_data
	 * @param stdClass|null $user_data
	 * @return void
	 */
	function validateCSRFTest(bool $expected, array $post_data, array $session_data, ?stdClass $user_data)
	{
		$_POST = $post_data;
		$_SESSION = $session_data;
		if ($expected) {
			$this->assertTrue(Validation::validateCSRF($user_data));
		}
		else {
			$this->assertFalse(Validation::validateCSRF($user_data));
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
}
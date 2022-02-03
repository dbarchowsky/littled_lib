<?php
namespace Littled\Tests\Validation;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Exception\ContentValidationException;
use Littled\Tests\Request\DataProvider\IntegerInputTestData;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;


class ValidationTest extends TestCase
{
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
     * @dataProvider \Littled\Tests\Validation\DataProvider\ValidationTestDataProvider::parseNumericTestProvider()
     * @param $expected
     * @param $value
     * @return void
     */
	public function testParseNumeric($expected, $value)
	{
        $this->assertEquals($expected, Validation::parseNumeric($value));
	}

	public function testIsStringWithContent()
	{
		$this->assertFalse(Validation::isStringWithContent(null));
		$this->assertFalse(Validation::isStringWithContent(false));
		$this->assertFalse(Validation::isStringWithContent(true));
		$this->assertFalse(Validation::isStringWithContent(0));
		$this->assertFalse(Validation::isStringWithContent(1));
		$this->assertFalse(Validation::isStringWithContent(435));
		$this->assertFalse(Validation::isStringWithContent(''));
		$this->assertTrue(Validation::isStringWithContent('a'));
		$this->assertTrue(Validation::isStringWithContent('foo biz bar bash'));
		$this->assertTrue(Validation::isStringWithContent('null'));
		$this->assertTrue(Validation::isStringWithContent('false'));
		$this->assertTrue(Validation::isStringWithContent('true'));
		$this->assertTrue(Validation::isStringWithContent('0'));
		$this->assertTrue(Validation::isStringWithContent('1'));
		$this->assertTrue(Validation::isStringWithContent('435'));
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
	 * @throws ContentValidationException
	 */
	public function testValidateDateString()
	{
		$d = Validation::validateDateString('2016-03-15');
		$this->assertEquals('2016-03-15', $d->format('Y-m-d'), "Y-m-d format");
		$d = Validation::validateDateString('3/15/2016');
		$this->assertEquals('2016-03-15', $d->format('Y-m-d'), "n/j/Y format");
		$d = Validation::validateDateString('03/15/2016');
		$this->assertEquals('2016-03-15', $d->format('Y-m-d'), "m/d/Y format");
		$d = Validation::validateDateString('3/2/2016');
		$this->assertEquals('2016-03-02', $d->format('Y-m-d'), "n/j/Y format");
		$d = Validation::validateDateString('02/08/1987');
		$this->assertEquals('1987-02-08', $d->format('Y-m-d'), "m/d/Y format");
		$d = Validation::validateDateString('2/8/87');
		$this->assertEquals('1987-02-08', $d->format('Y-m-d'), "n/j/y format");
		$d = Validation::validateDateString('02/08/87');
		$this->assertEquals('1987-02-08', $d->format('Y-m-d'), "m/d/y format");
		$d = Validation::validateDateString('February 08, 1987');
		$this->assertEquals('1987-02-08', $d->format('Y-m-d'), "F d, Y format");
		$d = Validation::validateDateString('February 8, 1987');
		$this->assertEquals('1987-02-08', $d->format('Y-m-d'), "F j, Y format");
		try {
			$d = Validation::validateDateString('February 08, 87');
		}
		catch (ContentValidationException $ex) {
			$this->assertEquals("Unrecognized date value.", $ex->getMessage(), "F d, y format");
		}
	}

	public function testCollectIntegerArrayRequestVar()
	{
		$src = array('int' => 44,
			'float' => 208.04,
			'int_array' => array(5, 6, 8, 3),
			'float_array' => array(1.5, 0.36, 10.05),
			'mixed_array' => array(4.5, 'test value', 10, 22.6));
		$result = Validation::collectIntegerArrayRequestVar('int', $src);
		$this->assertCount(1, $result);
		$this->assertEquals($src['int'], $result[0]);

		$result = Validation::collectIntegerArrayRequestVar('float', $src);
		$this->assertCount(0, $result);

		$result = Validation::collectIntegerArrayRequestVar('int_array', $src);
		$this->assertCount(4, $result);
		$this->assertEquals($src['int_array'][0], $result[0]);
		$this->assertEquals($src['int_array'][1], $result[1]);
		$this->assertEquals($src['int_array'][2], $result[2]);
		$this->assertEquals($src['int_array'][3], $result[3]);

		$result = Validation::collectIntegerArrayRequestVar('float_array', $src);
		$this->assertCount(0, $result);

		$result = Validation::collectIntegerArrayRequestVar('mixed_array', $src);
		$this->assertCount(1, $result);
		$this->assertEquals($src['mixed_array'][2], $result[0]);
	}

	public function testCollectNumericArrayRequestVar()
	{
		$src = array('int' => 44,
			'float' => 208.04,
			'int_array' => array(5, 6, 8, 3),
			'float_array' => array(1.5, 0.36, 10.05),
			'mixed_array' => array(4.5, 'test value', 10, 22.6));
		$result = Validation::collectNumericArrayRequestVar('int', $src);
		$this->assertCount(1, $result);
		$this->assertEquals($src['int'], $result[0]);

		$result = Validation::collectNumericArrayRequestVar('float', $src);
		$this->assertCount(1, $result);
		$this->assertEquals($src['float'], $result[0]);

		$result = Validation::collectNumericArrayRequestVar('int_array', $src);
		$this->assertCount(4, $result);
		$this->assertEquals($src['int_array'][0], $result[0]);
		$this->assertEquals($src['int_array'][1], $result[1]);
		$this->assertEquals($src['int_array'][2], $result[2]);
		$this->assertEquals($src['int_array'][3], $result[3]);

		$result = Validation::collectNumericArrayRequestVar('float_array', $src);
		$this->assertCount(3, $result);
		$this->assertEquals($src['float_array'][0], $result[0]);
		$this->assertEquals($src['float_array'][1], $result[1]);
		$this->assertEquals($src['float_array'][2], $result[2]);

		$result = Validation::collectNumericArrayRequestVar('mixed_array', $src);
		$this->assertCount(3, $result);
		$this->assertEquals($src['mixed_array'][0], $result[0]);
		$this->assertEquals($src['mixed_array'][2], $result[1]);
		$this->assertEquals($src['mixed_array'][3], $result[2]);
	}
}
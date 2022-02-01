<?php
namespace Littled\Tests\Validation;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Exception\ContentValidationException;
use Littled\Validation\Validation;
use PHPUnit\Framework\TestCase;


class ValidationTest extends TestCase
{
	public function testParseNumeric()
	{
		$int_overflow = (PHP_INT_MAX+1);

		$this->assertEquals(1, Validation::parseNumeric("1"), "\"1\" returns numeric value.");
		$this->assertEquals(0, Validation::parseNumeric("0"), "\"0\" returns numeric value.");
		$this->assertEquals(-1, Validation::parseNumeric("-1"));
		$this->assertEquals(5, Validation::parseNumeric("5"));
		$this->assertEquals(PHP_INT_MAX, Validation::parseNumeric("".PHP_INT_MAX), "parseNumeric() with largest possible integer value");
		// $this->assertEquals(Littled\Validation\Validation::parseNumeric("".(PHP_INT_MAX+1)), $int_overflow, "parseNumeric() with value overflowing int max value");
		$this->assertEquals(0.01, Validation::parseNumeric("0.01"));
		$this->assertEquals(4.5, Validation::parseNumeric("4.5"));
		$this->assertNull(Validation::parseNumeric("zero"));
		$this->assertNull(Validation::parseNumeric("j01"));
		$this->assertNull(Validation::parseNumeric("01jx"));
		$this->assertNull(Validation::parseNumeric("true"));
		$this->assertNull(Validation::parseNumeric("false"));
		$this->assertNull(Validation::parseNumeric(true));
		$this->assertNull(Validation::parseNumeric(false));
	}
	
	public function testIsInteger()
	{
		$this->assertTrue(Validation::isInteger(1));
		$this->assertTrue(Validation::isInteger(0));
		$this->assertTrue(Validation::isInteger(-1));
		$this->assertTrue(Validation::isInteger("1"));
		$this->assertTrue(Validation::isInteger("0"));
		$this->assertTrue(Validation::isInteger("-1"));
		$this->assertFalse(Validation::isInteger("-"));
		$this->assertFalse(Validation::isInteger("true"));
		$this->assertFalse(Validation::isInteger("false"));
		$this->assertFalse(Validation::isInteger(true));
		$this->assertFalse(Validation::isInteger(false));
		$this->assertFalse(Validation::isInteger(4.5));
		$this->assertFalse(Validation::isInteger('4.5'));
		$this->assertFalse(Validation::isInteger(null));
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

	public function testParseInteger()
	{
		$this->assertEquals(1, Validation::parseInteger(1));
		$this->assertEquals(0, Validation::parseInteger(0));
		$this->assertEquals(-1, Validation::parseInteger(-1));
		$this->assertEquals(1, Validation::parseInteger("1"));
		$this->assertEquals(0, Validation::parseInteger("0"));
		$this->assertEquals(-1, Validation::parseInteger("-1"));
		$this->assertNull(Validation::parseInteger("-"));
		$this->assertNull(Validation::parseInteger("true"));
		$this->assertNull(Validation::parseInteger("false"));
		$this->assertNull(Validation::parseInteger(true));
		$this->assertNull(Validation::parseInteger(false));
		$this->assertNull(Validation::parseInteger(4.5));
		$this->assertNull(Validation::parseInteger('4.5'));
		$this->assertNull(Validation::parseInteger(null));
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
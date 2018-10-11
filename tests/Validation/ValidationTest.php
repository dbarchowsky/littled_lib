<?php
use Littled\Validation\Validation;

class ValidationTest extends \PHPUnit\Framework\TestCase
{
	public function testParseNumeric()
	{
		$int_overflow = (PHP_INT_MAX+1);

		$this->assertEquals(Littled\Validation\Validation::parseNumeric("1"), 1, "\"1\" returns numeric value.");
		$this->assertEquals(Littled\Validation\Validation::parseNumeric("0"), 0, "\"0\" returns numeric value.");
		$this->assertEquals(Littled\Validation\Validation::parseNumeric("-1"), -1);
		$this->assertEquals(Littled\Validation\Validation::parseNumeric("5"), 5);
		$this->assertEquals(Littled\Validation\Validation::parseNumeric("".PHP_INT_MAX), PHP_INT_MAX, "parseNumeric() with largest possible integer value");
		// $this->assertEquals(Littled\Validation\Validation::parseNumeric("".(PHP_INT_MAX+1)), $int_overflow, "parseNumeric() with value overflowing int max value");
		$this->assertEquals(Littled\Validation\Validation::parseNumeric("0.01"), 0.01);
		$this->assertEquals(Littled\Validation\Validation::parseNumeric("4.5"), 4.5);
		$this->assertNull(Littled\Validation\Validation::parseNumeric("zero"));
		$this->assertNull(Littled\Validation\Validation::parseNumeric("j01"));
		$this->assertNull(Littled\Validation\Validation::parseNumeric("01jx"));
		$this->assertNull(Littled\Validation\Validation::parseNumeric("true"));
		$this->assertNull(Littled\Validation\Validation::parseNumeric("false"));
		$this->assertNull(Littled\Validation\Validation::parseNumeric(true));
		$this->assertNull(Littled\Validation\Validation::parseNumeric(false));
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

	public function testParseInteger()
	{
		$this->assertEquals(Validation::parseInteger(1), 1);
		$this->assertEquals(Validation::parseInteger(0), 0);
		$this->assertEquals(Validation::parseInteger(-1), -1);
		$this->assertEquals(Validation::parseInteger("1"), 1);
		$this->assertEquals(Validation::parseInteger("0"), 0);
		$this->assertEquals(Validation::parseInteger("-1"), -1);
		$this->assertNull(Validation::parseInteger("-"));
		$this->assertNull(Validation::parseInteger("true"));
		$this->assertNull(Validation::parseInteger("false"));
		$this->assertNull(Validation::parseInteger(true));
		$this->assertNull(Validation::parseInteger(false));
		$this->assertNull(Validation::parseInteger(4.5));
		$this->assertNull(Validation::parseInteger('4.5'));
		$this->assertNull(Validation::parseInteger(null));
	}

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
		catch (\Littled\Exception\ContentValidationException $ex) {
			$this->assertEquals("Unrecognized date value.", $ex->getMessage(), "F d, y format");
		}
	}
}
<?php
class ValidationTest extends PHPUnit_Framework_TestCase
{
	public function testParseNumeric()
	{
		$this->assertEquals(Littled\Validation\Validation::parse_numeric("1"), 1, "\"1\" returns numeric value.");
		$this->assertEquals(Littled\Validation\Validation::parse_numeric("0"), 0, "\"0\" returns numeric value.");
		$this->assertEquals(Littled\Validation\Validation::parse_numeric("-1"), -1);
		$this->assertEquals(Littled\Validation\Validation::parse_numeric("5"), 5);
		$this->assertEquals(Littled\Validation\Validation::parse_numeric("9757848484"), 9757848484);
		$this->assertEquals(Littled\Validation\Validation::parse_numeric("0.01"), 0.01);
		$this->assertEquals(Littled\Validation\Validation::parse_numeric("4.5"), 4.5);
		$this->assertNull(Littled\Validation\Validation::parse_numeric("zero"), "\"zero\" returns null");
		$this->assertNull(Littled\Validation\Validation::parse_numeric("j01"), "\"j01\" returns NULL");
		$this->assertNull(Littled\Validation\Validation::parse_numeric("01jx"), "\"01jx\" returns NULL");
	}
}
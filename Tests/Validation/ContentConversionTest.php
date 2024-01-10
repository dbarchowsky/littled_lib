<?php
namespace LittledTests\Validation;

use Littled\Validation\ContentConversion;
use PHPUnit\Framework\TestCase;

class ContentConversionTest extends TestCase
{
	/**
	 * @dataProvider \LittledTests\DataProvider\Validation\ContentConversionTestDataProvider::formatIndexMarkupProvider()
	 * @param string $expected
	 * @param mixed $index
	 * @return void
	 */
	function testFormatIndexMarkup(string $expected, $index)
	{
		$this->assertEquals($expected, ContentConversion::formatIndexMarkup($index));
	}
}
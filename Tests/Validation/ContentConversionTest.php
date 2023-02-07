<?php
namespace Littled\Tests\Validation;

use Littled\Validation\ContentConversion;
use PHPUnit\Framework\TestCase;

class ContentConversionTest extends TestCase
{
	/**
	 * @dataProvider \Littled\Tests\DataProvider\Validation\ContentConversionTestDataProvider::formatIndexMarkupProvider()
	 * @param string $expected
	 * @param mixed $index
	 * @return void
	 */
	function testFormatIndexMarkup(string $expected, $index)
	{
		$this->assertEquals($expected, ContentConversion::formatIndexMarkup($index));
	}
}
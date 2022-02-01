<?php
namespace Littled\Tests\Validation;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Validation\ContentConversion;
use PHPUnit\Framework\TestCase;

class ContentConversionTest extends TestCase
{
	/**
	 * @dataProvider \Littled\Tests\Validation\DataProvider\ContentConversionTestDataProvider::formatIndexMarkupProvider()
	 * @param string $expected
	 * @param mixed $index
	 * @return void
	 */
	function testFormatIndexMarkup(string $expected, $index)
	{
		$this->assertEquals($expected, ContentConversion::formatIndexMarkup($index));
	}
}
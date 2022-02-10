<?php
namespace Littled\Tests\PageContent\SiteSection;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\SiteSection\ContentRoute;
use PHPUnit\Framework\TestCase;


class ContentRouteTest extends TestCase
{
	function testConstructorAssignedPropertyValues()
	{
		$cr = new ContentRoute(99, 6037, 'foo', 'https://localhost');
		$this->assertEquals(99, $cr->id->value);
		$this->assertEquals(6037, $cr->site_section_id->value);
		$this->assertEquals('foo', $cr->operation->value);
		$this->assertEquals('https://localhost', $cr->url->value);
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testConstructorDefaultPropertyValues()
	{
		$cr = new ContentRoute();
		$this->assertNull($cr->id->value);
		$this->assertNull($cr->site_section_id->value);
		$this->assertEmpty($cr->url->value);
		$this->assertEmpty($cr->operation->value);

		$this->assertEquals(34, ContentRoute::getContentTypeId());
	}

	/**
	 * @dataProvider \Littled\Tests\PageContent\SiteSection\DataProvider\ContentRouteTestDataProvider::hasDataTestProvider()
	 * @param bool $expected
	 * @param int|null $id
	 * @param int|null $site_section_id
	 * @param string $operation
	 * @param string $url
	 * @return void
	 */
	function testHasData(bool $expected, ?int $id, ?int $site_section_id, string $operation='', string $url='')
	{
		$cr = new ContentRoute($id, $site_section_id, $operation, $url);
		if (true===$expected) {
			$this->assertTrue($cr->hasData());
		}
		else {
			$this->assertFalse($cr->hasData());
		}
	}
}
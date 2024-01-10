<?php
namespace LittledTests\PageContent\Navigation;

use Littled\App\LittledGlobals;
use Littled\Exception\ResourceNotFoundException;
use LittledTests\DataProvider\PageContent\Navigation\NavigationMenuNodeTestData;
use PHPUnit\Framework\TestCase;

class NavigationMenuNodeTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		LittledGlobals::setLocalTemplatesPath(LITTLED_TEMPLATE_DIR);
		LittledGlobals::setSharedTemplatesPath(LITTLED_TEMPLATE_DIR);
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\PageContent\Navigation\NavigationMenuNodeTestDataProvider::renderTestProvider()
	 * @param NavigationMenuNodeTestData $data
	 * @return void
	 * @throws ResourceNotFoundException
	 */
	function testRender(NavigationMenuNodeTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->node->render();
	}
}
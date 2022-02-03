<?php
namespace Littled\Tests\PageContent\Navigation;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\App\LittledGlobals;
use Littled\Exception\ResourceNotFoundException;
use Littled\Tests\PageContent\Navigation\DataProvider\NavigationMenuNodeTestData;
use PHPUnit\Framework\TestCase;

class NavigationMenuNodeTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		LittledGlobals::setTemplatePath(LITTLED_TEMPLATE_DIR);
	}

	/**
	 * @dataProvider \Littled\Tests\PageContent\Navigation\DataProvider\NavigationMenuNodeTestDataProvider::renderTestProvider()
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
<?php
namespace Littled\Tests\PageContent\Navigation;

use Littled\App\LittledGlobals;
use Littled\Exception\ResourceNotFoundException;
use Littled\Tests\PageContent\Navigation\DataProvider\BreadcrumbsNodeTestData;
use PHPUnit\Framework\TestCase;

class BreadcrumbsNodeTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		LittledGlobals::setLocalTemplatesPath(LITTLED_TEMPLATE_DIR);
		LittledGlobals::setSharedTemplatesPath(LITTLED_TEMPLATE_DIR);
	}

	/**
	 * @dataProvider \Littled\Tests\PageContent\Navigation\DataProvider\BreadcrumbsNodeTestDataProvider::renderTestProvider()
	 * @param BreadcrumbsNodeTestData $data
	 * @return void
	 * @throws ResourceNotFoundException
	 */
	function testRender(BreadcrumbsNodeTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->node->render();
	}
}
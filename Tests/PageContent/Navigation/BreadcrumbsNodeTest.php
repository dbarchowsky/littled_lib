<?php
namespace Littled\Tests\PageContent\Navigation;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\App\LittledGlobals;
use Littled\Exception\ResourceNotFoundException;
use Littled\Tests\PageContent\Navigation\DataProvider\BreadcrumbsNodeTestData;
use PHPUnit\Framework\TestCase;

class BreadcrumbsNodeTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		LittledGlobals::setTemplatePath(LITTLED_TEMPLATE_DIR);
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
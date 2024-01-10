<?php
namespace LittledTests\PageContent\Navigation;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use LittledTests\DataProvider\PageContent\Navigation\BreadcrumbsNodeTestData;
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
     * @dataProvider \LittledTests\DataProvider\PageContent\Navigation\BreadcrumbsNodeTestDataProvider::renderTestProvider()
     * @param BreadcrumbsNodeTestData $data
     * @return void
     * @throws ResourceNotFoundException
     * @throws ConfigurationUndefinedException
     */
	function testRender(BreadcrumbsNodeTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->node->render();
	}
}
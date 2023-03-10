<?php
namespace Littled\Tests\PageContent\Navigation;

use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Navigation\SectionNavigationRoutes;
use Littled\Tests\DataProvider\PageContent\Navigation\SectionNavigationRoutes\GetPageRouteTestData;
use Littled\Tests\TestHarness\PageContent\Navigation\SectionNavigationRoutesTestHarness;
use Littled\Tests\TestHarness\SiteContent\TestTableDetailsPage;
use Littled\Tests\TestHarness\SiteContent\TestTableListingsPage;
use PHPUnit\Framework\TestCase;


class SectionNavigationRoutesTest extends TestCase
{
    function testGetDetailsRouteBase()
    {
        $this->assertEquals(TestTableDetailsPage::getBaseRoute(), SectionNavigationRoutesTestHarness::getDetailsRouteBase());
    }

    function testGetListingsRouteBase()
    {
        $this->assertEquals(TestTableListingsPage::getBaseRoute(), SectionNavigationRoutesTestHarness::getListingsRouteBase());
    }

	/**
	 * @dataProvider \Littled\Tests\DataProvider\PageContent\Navigation\SectionNavigationRoutesTestDataProvider::getPageRouteTestProvider()
	 * @param GetPageRouteTestData $data
	 * @return void
	 * @throws InvalidTypeException
	 */
	function testGetPageRoute(GetPageRouteTestData $data)
	{
		$this->assertEquals(
			$data->expected,
			SectionNavigationRoutesTestHarness::getPageRoute($data->class, $data->record_id));
	}

	function testGetTemplateDir()
	{
		$this->assertEquals('', SectionNavigationRoutes::getTemplateDir());
	}

	function testSetTemplateDir()
	{
		$new_path = '/new/path/to/templates/';
		SectionNavigationRoutes::setTemplateDir($new_path);
		$this->assertEquals($new_path, SectionNavigationRoutes::getTemplateDir());
	}
}
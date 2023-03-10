<?php
namespace Littled\Tests\DataProvider\PageContent\Navigation;


use Littled\Tests\DataProvider\PageContent\Navigation\SectionNavigationRoutes\GetPageRouteTestData;
use Littled\Tests\TestHarness\SiteContent\TestTableDetailsPage;
use Littled\Tests\TestHarness\SiteContent\TestTableEditPage;
use Littled\Tests\TestHarness\SiteContent\TestTableListingsPage;

class SectionNavigationRoutesTestDataProvider
{
	public static function getPageRouteTestProvider(): array
	{
		return array_map(
			function ($e) { return array($e); }, array(
			new GetPageRouteTestData('/tests', TestTableListingsPage::class),
			new GetPageRouteTestData('/test/45', TestTableDetailsPage::class, 45),
			new GetPageRouteTestData('/test/add', TestTableEditPage::class),
			new GetPageRouteTestData('/test/82/edit', TestTableEditPage::class, 82),
		));
	}
}
<?php
namespace LittledTests\DataProvider\PageContent\Navigation;


use LittledTests\DataProvider\PageContent\Navigation\SectionNavigationRoutes\GetPageRouteTestData;
use LittledTests\TestHarness\SiteContent\TestTableDetailsPage;
use LittledTests\TestHarness\SiteContent\TestTableEditPage;
use LittledTests\TestHarness\SiteContent\TestTableListingsPage;

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
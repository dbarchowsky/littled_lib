<?php
namespace LittledTests\TestHarness\PageContent\Navigation;

use Littled\PageContent\Navigation\SectionNavigationRoutes;
use LittledTests\PageContent\Navigation\RoutedPageContentTest;
use LittledTests\TestHarness\SiteContent\TestTableDetailsPage;
use LittledTests\TestHarness\SiteContent\TestTableEditPage;
use LittledTests\TestHarness\SiteContent\TestTableListingsPage;


class SectionNavigationRoutesTestHarness extends SectionNavigationRoutes
{
    protected static string $details_page_class = TestTableDetailsPage::class;
    protected static string $edit_page_class    = TestTableEditPage::class;
    protected static string $listings_page_class = TestTableListingsPage::class;
	protected static string $template_dir       = RoutedPageContentTest::TEST_TEMPLATE_DIR;

	public static function methodAvailableForTestPurposes() {}
}
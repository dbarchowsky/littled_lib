<?php
namespace Littled\Tests\TestHarness\PageContent\Navigation;

use Littled\PageContent\Navigation\SectionNavigationRoutes;
use Littled\Tests\PageContent\Navigation\RoutedPageContentTest;
use Littled\Tests\TestHarness\SiteContent\TestTableDetailsPage;
use Littled\Tests\TestHarness\SiteContent\TestTableEditPage;
use Littled\Tests\TestHarness\SiteContent\TestTableListingsPage;


class SectionNavigationRoutesTestHarness extends SectionNavigationRoutes
{
    protected static string $details_page_class = TestTableDetailsPage::class;
    protected static string $edit_page_class    = TestTableEditPage::class;
    protected static string $listings_page_class = TestTableListingsPage::class;
	protected static string $template_dir       = RoutedPageContentTest::TEST_TEMPLATE_DIR;

	public static function methodAvailableForTestPurposes() {}
}
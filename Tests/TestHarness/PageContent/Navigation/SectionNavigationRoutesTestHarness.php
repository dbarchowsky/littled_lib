<?php
namespace Littled\Tests\TestHarness\PageContent\Navigation;

use Littled\PageContent\Navigation\SectionNavigationRoutes;
use Littled\Tests\PageContent\Navigation\RoutedPageContentTest;

class SectionNavigationRoutesTestHarness extends SectionNavigationRoutes
{
    protected static string $listings_route     = '/unicorns';
    protected static string $details_route      = '/unicorn';
    protected static string $ed      = '/unicorn';
	protected static string $template_dir       = RoutedPageContentTest::TEST_TEMPLATE_DIR;

	public static function methodAvailableForTestPurposes() {}
}
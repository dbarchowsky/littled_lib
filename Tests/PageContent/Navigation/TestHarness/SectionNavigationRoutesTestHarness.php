<?php
namespace Littled\Tests\PageContent\Navigation\TestHarness;

use Littled\PageContent\Navigation\SectionNavigationRoutes;
use Littled\Tests\PageContent\Navigation\RoutedPageContentTest;

class SectionNavigationRoutesTestHarness extends SectionNavigationRoutes
{
    protected static string $details_route      = '/unicorn';
    protected static string $listings_route     = '/unicorns';
	protected static string $template_dir       = RoutedPageContentTest::TEST_TEMPLATE_DIR;
}
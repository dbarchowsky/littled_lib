<?php
namespace Littled\Tests\TestHarness\PageContent\Navigation;

use Littled\PageContent\Navigation\SectionNavigationRoutes;
use Littled\Tests\PageContent\Navigation\RoutedPageContentTest;
use Littled\Utility\LittledUtility;

class SectionNavigationRoutesTestHarness extends SectionNavigationRoutes
{
    protected static string $listings_route     = '/unicorns';
    protected static string $details_route      = '/unicorn';
    protected static string $ed      = '/unicorn';
	protected static string $template_dir       = RoutedPageContentTest::TEST_TEMPLATE_DIR;

	public static function methodAvailableForTestPurposes() {}

    public static function getDetailsRoute(int $record_id=null): string
    {
        return LittledUtility::joinPaths('/', static::$details_route, $record_id);
    }

    public static function getEditRoute(?int $record_id=null): string
    {
        return LittledUtility::joinPaths('/', static::$details_route, $record_id);
    }

    public static function getListingsRoute(): string
    {
        return static::$details_route;
    }
}
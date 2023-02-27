<?php
namespace Littled\Tests\TestHarness\PageContent\Navigation;

use Littled\PageContent\Navigation\SectionNavigationRoutes;


class TestTableSectionNavigationRoutes extends SectionNavigationRoutes
{
	protected static string $details_page_class='';
	protected static string $details_route='/test';
	protected static string $edit_page_class='';
	protected static string $listings_page_class='Littled\Tests\PageContent\RoutedPageContentTestHarness';
	protected static string $listings_route='/tests';

    public static function getDetailsRoute(?int $record_id = null): string
    {
        return static::$details_route;
    }

    public static function getEditRoute(?int $record_id = null): string
    {
        return static::$edit_route;
    }

    public static function getListingsRoute(): string
    {
        return static::$listings_route;
    }
}
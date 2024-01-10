<?php
namespace LittledTests\TestHarness\SiteContent;

use LittledTests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use LittledTests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use Littled\Utility\LittledUtility;


class TestTableListingsPage extends RoutedPageContentTestHarness
{
    protected static string $content_class = TestTableSectionContentTestHarness::class;
    protected static string $filters_class = TestTableContentFiltersTestHarness::class;
    protected static string $routes_class = TestTableSectionNavigationRoutes::class;
    protected static array $route_parts = ['tests'];

    public static function formatRoutePath(?int $record_id = null): string
    {
        return LittledUtility::joinPaths('/', ...static::$route_parts);
    }
}
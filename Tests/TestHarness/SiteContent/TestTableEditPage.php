<?php
namespace LittledTests\TestHarness\SiteContent;

use LittledTests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use LittledTests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;
use Littled\Utility\LittledUtility;


class TestTableEditPage extends RoutedPageContentTestHarness
{
	protected static string $content_class = TestTableSerializedContentTestHarness::class;
	protected static string $filters_class = TestTableContentFiltersTestHarness::class;
	protected static string $routes_class = TestTableSectionNavigationRoutes::class;
    protected static array  $route_parts = ['test'];
    const                   ADD_TOKEN    = 'add';
    const                   EDIT_TOKEN = 'edit';

    public static function formatRoutePath(?int $record_id = null): string
    {
        if ($record_id) {
            $route_parts = array_merge(static::$route_parts, array($record_id, self::EDIT_TOKEN));
        }
        else {
            $route_parts = array_merge(static::$route_parts, array(self::ADD_TOKEN));
        }
        return LittledUtility::joinPaths('/', ...$route_parts);
    }
}
<?php
namespace Littled\Tests\TestHarness\SiteContent;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Tests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use Littled\Utility\LittledUtility;


class TestTableDetailsPage extends RoutedPageContentTestHarness
{
    protected static string $content_class = TestTableSectionContentTestHarness::class;
    protected static string $filters_class = TestTableContentFiltersTestHarness::class;
    protected static string $routes_class = TestTableSectionNavigationRoutes::class;
    protected static array $route_parts = ['test'];

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    public static function formatRoutePath(?int $record_id = null): string
    {
        if ($record_id===null) {
            throw new ConfigurationUndefinedException('Record id not provided for details route.');
        }
        return LittledUtility::joinPaths('/', ...array_merge(static::$route_parts, array($record_id)));
    }
}
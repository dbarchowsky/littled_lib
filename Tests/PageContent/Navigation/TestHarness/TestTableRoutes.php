<?php
namespace Littled\Tests\PageContent\Navigation\TestHarness;

use Littled\PageContent\Navigation\SectionNavigationRoutes;


class TestTableRoutes extends SectionNavigationRoutes
{
    protected static string $details_page_class='';
    protected static string $details_route='/test';
    protected static string $edit_page_class='';
    protected static string $listings_page_class='Littled\Tests\PageContent\RoutedPageConentTestHarness';
    protected static string $listings_route='/tests';

}
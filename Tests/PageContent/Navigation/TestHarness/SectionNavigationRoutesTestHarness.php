<?php
namespace Littled\Tests\PageContent\Navigation\TestHarness;

use Littled\PageContent\Navigation\SectionNavigationRoutes;

class SectionNavigationRoutesTestHarness extends SectionNavigationRoutes
{
    protected static string $details_route='/unicorn';
    protected static string $listings_route='/unicorns';
}
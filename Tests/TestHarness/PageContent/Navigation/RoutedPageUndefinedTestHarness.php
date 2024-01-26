<?php

namespace LittledTests\TestHarness\PageContent\Navigation;


class RoutedPageUndefinedTestHarness extends RoutedPageContentTestHarness
{
    protected static string $routes_class = SectionNavRoutesUndefinedTestHarness::class;
}
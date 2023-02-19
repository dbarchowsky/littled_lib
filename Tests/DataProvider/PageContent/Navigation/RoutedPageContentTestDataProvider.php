<?php
namespace Littled\Tests\DataProvider\PageContent\Navigation;

class RoutedPageContentTestDataProvider
{
    public static function collectActionFromRouteTestProvider(): array
    {
        return array(
            ['', null, array('base')],
            ['', 123, array('base', '123')],
            ['edit', 123, array('base', '123', 'edit')],
            ['add', null, array('base', 'add')],
        );
    }

    public static function collectRecordIdFromRouteTestProvider(): array
    {
        return array(
            [null, array('base')],
            [123, array('base', '123')],
            [123, array('base', '123', 'edit')],
            [null, array('base', 'add')],
            [null, array('base', 'view', '123')],
            [123, array('base', '123', 'many', 'other', 'parts')],
        );
    }

    public static function getRecordIdProvider(): array
    {
        return array(
            [null, null],
            [0, null],
            [45, 45]
        );
    }
}
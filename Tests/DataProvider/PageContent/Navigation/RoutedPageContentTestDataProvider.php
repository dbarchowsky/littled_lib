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

    public static function getListingsURIWithFilters(): array
    {
        return array(
            array(['p=1', 'pl=25'], ['kw']),
            array(['p=1', 'pl=25', 'kw=foo', 'next=view'], [], array('kw' => 'foo')),
            array(['p=1', 'pl=25', 'kw=foo'], ['next'], array('kw' => 'foo'), array('next')),
            array(['p=1', 'pl=25'], ['next', 'kw'], array('kw' => 'foo'), array('next', 'kw')),
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
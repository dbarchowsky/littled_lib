<?php

namespace Littled\Tests\DataProvider\PageContent\SiteSection;


class ContentRouteTestDataProvider
{
	public static function hasDataTestProvider(): array
	{
        return array_map(function(ContentRouteTestData $o) { return $o->mapHasDataTestData(); }, array(
            ContentRouteTestData::newInstance(null, null, '', '', '', 'no properties set')
                ->setBoolExpectation(false),
            ContentRouteTestData::newInstance(null, 6037, '', '', '', 'site section id')
                ->setBoolExpectation(false),
            ContentRouteTestData::newInstance(99, null, '', '', '', 'record id')
                ->setBoolExpectation(true),
            ContentRouteTestData::newInstance(null, null, 'test-operation', '', '', 'operation')
                ->setBoolExpectation(true),
            ContentRouteTestData::newInstance(null, null, '', '', 'https://localhost', 'url')
                ->setBoolExpectation(true),
            ContentRouteTestData::newInstance(null, 6037, '', '', 'https://localhost', 'site section and url')
                ->setBoolExpectation(true),
            ContentRouteTestData::newInstance(null, 6037, 'test-operation', '', 'https://localhost', 'site section, operation, and url')
                ->setBoolExpectation(true),
            ContentRouteTestData::newInstance(null, 6037, 'test-operation', 'my-route', 'https://localhost')
                ->setBoolExpectation(true),
            ContentRouteTestData::newInstance(null, null, 'test-operation', 'my-route', 'https://localhost')
                ->setBoolExpectation(true),
            ContentRouteTestData::newInstance(null, null, '', 'my-route', '', 'route')
                ->setBoolExpectation(true),
        ));
	}

    public static function fetchRecordTestProvider(): array
    {
        return array_map(function(ContentRouteTestData $o) { return $o->mapFetchRecordTestData(); }, array(
                ContentRouteTestData::newInstance()
                    ->setExpectations(null, 6037, 'details', '/^\/test\/\[#\]$/', '/.*details.php$/')
                    ->setInputRecordId(61),
                ContentRouteTestData::newInstance()
                    ->setExpectations(null, 3, 'listings', '/^$/', '/.*listings.php$/')
                    ->setInputRecordId(8),
            )
        );
    }
}
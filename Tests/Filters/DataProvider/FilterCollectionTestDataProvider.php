<?php

namespace Littled\Tests\Filters\DataProvider;


use Exception;
use Littled\Tests\DataProvider\Filters\RetrieveNeighborIdsTestData;
use Littled\Tests\Filters\FilterCollectionRetrievalTest;
use Littled\Tests\Filters\TestHarness\TestTableFilters;

class FilterCollectionTestDataProvider
{
	public static function collectDisplayListingsSettingsWithAutoload(): array
	{
		return array(
			array(true, 'default', null, 'Default value.'),
			array(true, 'cookie', null, 'Ignoring cookie values.'),
			array(true, 'post', '1', 'With valid boolean TRUE value in POST data.'),
			array(false, 'post', '0', 'With valid boolean FALSE value in POST data.'),
			array(true, 'post', 'filter', 'With valid string filter value in POST data.'),
			array(true, 'post', 'foo', 'With invalid string value in POST data.'),
		);
	}

	public static function collectDisplayListingsSettingsWithDefault(): array
	{
		return array(
			array(null, 'default', null, 'Default value.'),
			array(null, 'cookie', null, 'Ignoring cookie values.'),
			array(true, 'post', '1', 'With valid boolean TRUE value in POST data.'),
			array(false, 'post', '0', 'With valid boolean FALSE value in POST data.'),
			array(true, 'post', 'filter', 'With valid string filter value in POST data.'),
			array(null, 'post', 'foo', 'With invalid string value in POST data.'),
		);
	}

    public static function retrieveNeighborIdsTestProvider(): array
    {
        // results are dependent on records returned by procedure testTableListingsSelect
        return array(
            array(new RetrieveNeighborIdsTestData(12, 23, 2583, 2217, 3025, 3, 5, null, '2nd record on page 3 (5 rpp)')),
            array(new RetrieveNeighborIdsTestData(12, 23, 2583, 2217, 3025, 2, 10, null, '12th record on page 2 (10 rpp))')),
            array(new RetrieveNeighborIdsTestData(1, 23, null, 2204, 2023, 1, 20, null, '1st record on page 1 (20 rpp)')),
            array(new RetrieveNeighborIdsTestData(1, 23, null, 2204, 2023, 1, 4, null, '1st record on page 1 (4 rpp)')),
            array(new RetrieveNeighborIdsTestData(2, 23, 2023, 2211, 2204, 1, 7, null, '2nd record on page 2 (7 rpp)')),
            array(new RetrieveNeighborIdsTestData(5, 23, 2209, 2205, 2206, 1, 5, null, 'last record on page 1 (5 rpp)')),
            array(new RetrieveNeighborIdsTestData(10, 23, 2216, 2583, 2213, 2, 5, null, 'last record on page 2 (5 rpp)')),
            array(new RetrieveNeighborIdsTestData(18, 23, 3025, 2214, 2217, 4, 5, null, '3rd record on page 4 (5 rpp)')),
            array(new RetrieveNeighborIdsTestData(18, 23, 3025, 2214, 2217, 3, 8, null, '2nd record on page 3 (8 rrp)')),
            array(new RetrieveNeighborIdsTestData(23, 23, 2208, null, 2624, 5, 5, null, '3rd record on last page (5 rpp)')),
            array(new RetrieveNeighborIdsTestData(23, 23, 2208, null, 2624, 3, 11, null, '1st record on last page (11 rrp)')),
            array(new RetrieveNeighborIdsTestData(1, 6, null, 2211, 2204, 1, 5, '%oo%', '1st record on first page of filtered listings (5 rrp)')),
            array(new RetrieveNeighborIdsTestData(1, 6, null, 2211, 2204, 1, 10, '%oo%', '1st record on first page of filtered listings (10 rrp)')),
            array(new RetrieveNeighborIdsTestData(5, 6, 2210, 2208, 2213, 2, 4, '%oo%', '1st record on last page of filtered listings (4 rrp)')),
            array(new RetrieveNeighborIdsTestData(6, 6, 2213, null, 2208, 2, 4, '%oo%', 'last record on last page of filtered listings (4 rrp)')),
        );
    }
}
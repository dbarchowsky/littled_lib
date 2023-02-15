<?php /** @noinspection PhpRedundantOptionalArgumentInspection */

namespace Littled\Tests\DataProvider\Filters;


class FilterCollectionTestDataProvider
{
	public static function calculateOffsetToPageTestProvider(): array
	{
		return array(
			[1, 10, 0],
			[5, 10, 40],
			[10, 10, 90],
			[11, 10, 100],
			[9, 46, 368],
		);
	}

    public static function collectDisplayListingsSettingTestProvider(): array
    {
        return array(
            array(null, [], [], null, 'no data'),
            array(true, array('filter' => '1'), [], null, 'GET data'),
            array(true, [], array('filter' => '1'), null, 'POST data TRUE'),
            array(false, [], array('filter' => '0'), null, 'POST data FALSE'),
            array(false, [], array('filter' => '1'), array('filter' => '0'), 'custom request data'),
            array(true, array('route' => '/some/random/data'), [], array('filter' => '1'), 'custom request data'),
        );
    }

	public static function collectDisplayListingsSettingsWithAutoload(): array
	{
		return array(
			array(true, 'default', null, 'Default value.'),
			array(true, 'cookie', null, 'Ignoring cookie values.'),
			array(true, 'post', '1', 'With valid boolean TRUE ("1") value in POST data.'),
			array(false, 'post', '0', 'With valid boolean FALSE ("0") value in POST data.'),
			array(true, 'post', 'filter', 'With valid string "filter" value in POST data.'),
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

    public static function collectFilterValuesTestProvider(): array
    {
        return array(
            array(
                array('name_filter' => '', 'bool_filter' => null),
                [], [], null, true, [],
                'no data'),
            array(
                array('name_filter' => 'foo', 'bool_filter' => null),
                array('nameFilter' => 'foo'), [], null, true, [],
                'GET data'),
            array(
                array('name_filter' => 'bar', 'bool_filter' => null),
                [], array('nameFilter' => 'bar'), null, true, [],
                'POST data'),
            array(
                array('name_filter' => 'biz', 'bool_filter' => null),
                array('nameFilter' => 'foo'), [],
                array('nameFilter' => 'biz'), true, [],
                'custom data over GET data'),
            array(
                array('name_filter' => 'bash', 'bool_filter' => null),
                [], array('nameFilter' => 'bar'),
                array('nameFilter' => 'bash'), true, [],
                'custom data over POST data'),
            array(
                array('name_filter' => 'bash', 'bool_filter' => true),
                [], array('nameFilter' => 'bar'),
                array('nameFilter' => 'bash', 'boolFilter' => 1), true, [],
                'custom data over POST data with multiple values'),
        );
    }


	public static function calculateRecordPositionOnPageTestProvider(): array
	{
		// results are dependent on records returned by procedure testTableListingsSelect
		return array_map(
			function(FilterCollectionTestData $o) { return $o->mapCalculateRecordPositionOnPageTestData(); },
			array(
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(3025, 3, 5, '')
					->setExpectations(5),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(3025, 2, 10, '')
					->setExpectations(5),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2023, 1, 20, '')
					->setExpectations(1),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2023, 1, 4, '')
					->setExpectations(1),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2204, 1, 7, '')
					->setExpectations(2),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2206, 1, 5, '')
					->setExpectations(5),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2213, 3, 5, '')
					->setExpectations(3),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 3, 12, '')
					->setExpectations(null),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2214, 2, 12, '')
					->setExpectations(12),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 4, 6, '')
					->setExpectations(5),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2624, 6, 5, '')
					->setExpectations(3),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2624, 1, 28, '')
					->setExpectations(28),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2204, 1, 5, '%oo%')
					->setExpectations(1),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2204, 1, 10, '%oo%')
					->setExpectations(1),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2213, 2, 4, '%oo%')
					->setExpectations(1),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2208, 2, 4, '%oo%')
					->setExpectations(2),
			)
		);
	}

	public static function listingsDataContainsNeighborIdsTestProvider(): array
	{
		return array(
			array(true, array(1,2,3,4,5), 1, 1, 10, 1, 5, '1st record, page 1/1'),
			array(true, array(1,2,3,4,5), 2, 1, 10, 1, 5, '2nd record, page 1/1'),
			array(true, array(1,2,3,4,5), 4, 1, 10, 1, 5, '2nd to last record, page 1/1'),
			array(true, array(1,2,3,4,5), 5, 1, 10, 1, 5, 'last record, page 1/1'),
			array(false, array(1,2,3,4,5), 8, 1, 10, 1, 5, 'out of upper bounds, page 1/1'),
			array(false, array(1,2,3,4,5), 1, 2, 10, 2, 15, '1st record, page 2/2'),
			array(true, array(1,2,3,4,5), 2, 2, 10, 2, 15, '2nd record, page 2/2'),
			array(true, array(1,2,3,4,5), 4, 2, 10, 2, 15, '2nd to last record, page 2/2'),
			array(true, array(1,2,3,4,5), 5, 2, 10, 2, 15, 'last record, page 2/2'),
			array(false, array(1,2,3,4,5,6,7,8,9,10), 1, 2, 10, 3, 25, '1st record, page 2/3'),
			array(true, array(1,2,3,4,5,6,7,8,9,10), 2, 2, 10, 3, 25, '1st record, page 2/3'),
			array(true, array(1,2,3,4,5,6,7,8,9,10), 9, 2, 10, 3, 25, '2nd to last record, page 2/3'),
			array(false, array(1,2,3,4,5,6,7,8,9,10), 10, 2, 10, 3, 25, 'last record, page 2/3'),
			array(true, array(1), 1, 1, 1, 1, 1, '1st record, single page, single listing'),
			array(false, array(1), 1, 1, 1, 1, 2, '1st record, page 1/2, one record per page'),
			array(false, array(1), 1, 2, 1, 2, 3, '1st record, page 2/3, one record per page'),
			array(false, array(1), 1, 3, 1, 3, 3, '1st record, page 3/3, one record per page'),
		);
	}

    public static function retrieveNeighborIdsTestProvider(): array
    {
        // results are dependent on records returned by procedure testTableListingsSelect
		return array_map(
			function(FilterCollectionTestData $o) { return $o->mapRetrieveNeighborIdsTestData(); },
			array(
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(3025, 3, 5, '')
					->setExpectations(4, 23, 2583, 6406),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(3025, 2, 10, '')
					->setExpectations(4, 23, 2583, 6406),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2023, 1, 20, '')
					->setExpectations(1, 23, null, 2204),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2023, 1, 4, '')
					->setExpectations(1, 23, null, 2204),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2204, 1, 7, '')
					->setExpectations(2, 23, 2023, 2211),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2206, 1, 5, '')
					->setExpectations(5, 23, 2209, 2205),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2213, 3, 5, '')
					->setExpectations(2, 23, 6010, 2583),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 3, 5, '')
					->setExpectations(null, 23, 6410, 2214),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 4, 5, '')
					->setExpectations(5, 23, 6410, 2214),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 3, 7, '')
					->setExpectations(6, 23, 6410, 2214),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2624, 5, 5, '')
					->setExpectations(5, 23, 2208, null),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2624, 3, 11, '')
					->setExpectations(3, 23, 2208, null),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2204, 1, 5, '%oo%')
					->setExpectations(1, 6, null, 2211),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2204, 1, 10, '%oo%')
					->setExpectations(1, 6, null, 2211),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2213, 2, 4, '%oo%')
					->setExpectations(1, 6, 2210, 2208),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2208, 2, 4, '%oo%')
					->setExpectations(2, 6, 2213, null),
			)
		);
    }

	public static function setOutOfBoundNeighborIdsTestProvider():array
	{
		return array_map(
			function(FilterCollectionTestData $o) { return $o->mapSetOutOfBoundsNeighborIdsTestData(); },
			array(
				FilterCollectionTestData::newInstance()
					->setSetOutOfBoundNeighborIdsTestData(2205, 2, 5)
					->setExpectations(null, 0, 2206, 2210),
				FilterCollectionTestData::newInstance()
					->setSetOutOfBoundNeighborIdsTestData(2023, 1, 5)
					->setExpectations(null, 0, null, 2204),
				FilterCollectionTestData::newInstance()
					->setSetOutOfBoundNeighborIdsTestData(2204, 1, 5)
					->setExpectations(null, 0, 2023, 2211),
				FilterCollectionTestData::newInstance()
					->setSetOutOfBoundNeighborIdsTestData(2209, 1, 5)
					->setExpectations(null, 0, 2211, 2206),
				FilterCollectionTestData::newInstance()
					->setSetOutOfBoundNeighborIdsTestData(2206, 1, 5)
					->setExpectations(null, 0, 2209, 2205),
				FilterCollectionTestData::newInstance()
					->setSetOutOfBoundNeighborIdsTestData(2624, 4, 7)
					->setExpectations(null, 0, 2208, null),
				FilterCollectionTestData::newInstance()
					->setSetOutOfBoundNeighborIdsTestData(2624, 6, 5)
					->setExpectations(null, 0, 2208, null),
			)
		);
	}
}
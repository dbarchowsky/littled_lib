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

	public static function calculateRecordPositionOnPageTestProvider(): array
	{
		// results are dependent on records returned by procedure testTableListingsSelect
		return array_map(
			function(FilterCollectionTestData $o) { return $o->mapCalculateRecordPositionOnPageTestData(); },
			array(
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(3025, 3, 5, '')
					->setExpectations(4),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(3025, 2, 10, '')
					->setExpectations(4),
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
					->setExpectations(2),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 3, 5, '')
					->setExpectations(null),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 4, 5, '')
					->setExpectations(5),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2217, 3, 7, '')
					->setExpectations(6),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2624, 5, 5, '')
					->setExpectations(5),
				FilterCollectionTestData::newInstance()
					->setRetrieveNeighborIdsTestData(2624, 3, 11, '')
					->setExpectations(3),
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
}
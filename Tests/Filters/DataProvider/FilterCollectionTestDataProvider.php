<?php

namespace Littled\Tests\Filters\DataProvider;


use Exception;
use Littled\Tests\Filters\FilterCollectionRetrievalTest;
use Littled\Tests\Filters\TestHarness\TestTableFilters;

class FilterCollectionTestDataProvider
{
	/**
	 * @throws Exception
	 */
	public static function retrieveNeighborIdsTestProvider(): array
	{
		$f = new TestTableFilters();
		$f->page->value = 1;
		$f->listings_length->value = FilterCollectionRetrievalTest::TEST_LISTINGS_LENGTH;

		$p1_data = $f->retrieveListings();

		$f->page->value = 2;
		$mid_data = $f->retrieveListings();

		$f->page->value = $f->page_count-1;
		$p3_data = $f->retrieveListings();

		$f->page->value = $f->page_count;
		$end_data = $f->retrieveListings();
		$end_index = count($end_data)-1;

		return array(
			[null, $p1_data[1]->id, $p1_data[0]->id, 1, 'first page, first record on page'],
			[$p1_data[0]->id, $p1_data[2]->id, $p1_data[1]->id, 1, 'first page, 2nd record on page'],
			[$p1_data[1]->id, $p1_data[3]->id, $p1_data[2]->id, 1,'first page, middle record on page'],
			[$p1_data[FilterCollectionRetrievalTest::TEST_LISTINGS_LENGTH-2]->id, $mid_data[0]->id, $p1_data[FilterCollectionRetrievalTest::TEST_LISTINGS_LENGTH-1]->id, 1, 'first page, last record on page'],
			[$p1_data[FilterCollectionRetrievalTest::TEST_LISTINGS_LENGTH-1]->id, $mid_data[1]->id, $mid_data[0]->id, 2, '2nd page, first record on page'],
			[$mid_data[0]->id, $mid_data[2]->id, $mid_data[1]->id, 2, '2nd page, 2nd record on page'],
			[$mid_data[1]->id, $mid_data[3]->id, $mid_data[2]->id, 2,'2nd page, middle record on page'],
			[$mid_data[FilterCollectionRetrievalTest::TEST_LISTINGS_LENGTH-2]->id, $p3_data[0]->id, $mid_data[FilterCollectionRetrievalTest::TEST_LISTINGS_LENGTH-1]->id, 2, '2nd page, last record on page'],
			[$p3_data[FilterCollectionRetrievalTest::TEST_LISTINGS_LENGTH-1]->id, $end_data[1]->id, $end_data[0]->id, $f->page_count, 'last page, first record on page'],
			[$end_data[0]->id, $end_data[2]->id, $end_data[1]->id, $f->page_count, 'last page, middle record on page'],
			[$end_data[$end_index-1]->id, null, $end_data[$end_index]->id, $f->page_count, 'last page, last record on page'],
		);
	}
}
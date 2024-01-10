<?php

namespace LittledTests\TestHarness\Filters;

use Exception;
use Littled\Filters\BooleanContentFilter;
use Littled\Filters\DateContentFilter;
use Littled\Filters\FilterCollection;
use Littled\Filters\IntegerContentFilter;
use Littled\Filters\StringContentFilter;

class FilterCollectionChild extends FilterCollection
{
    public StringContentFilter $name_filter;
    public IntegerContentFilter $int_filter;
    public BooleanContentFilter $bool_filter;
    public DateContentFilter $date_after;
    public DateContentFilter $date_before;
    protected static string $table_name='test_table';
    protected static int $default_listings_length = 15;

    public function __construct()
    {
        parent::__construct();
        $this->name_filter = new StringContentFilter('name', 'nameFilter', '', 50, $this::getCookieKey());
        $this->int_filter = new IntegerContentFilter('integer column', 'intFilter', null, null, $this::getCookieKey());
        $this->bool_filter = new BooleanContentFilter('boolean column', 'boolFilter', null, null, $this::getCookieKey());
        $this->date_after = new DateContentFilter('date after', 'dateAfter', '', null, $this::getCookieKey());
        $this->date_before = new DateContentFilter('date before', 'dateBefore', '', null, $this::getCookieKey());
    }

	/**
	 * @inheritDoc
	 * Make this public for test classes.
	 */
	public function collectDisplayListingsSetting(?array $src=null)
	{
		parent::collectDisplayListingsSetting($src);
	}

	/**
     * @return array
     */
    public function formatListingsQueryTest(): array
    {
        return $this->formatListingsQuery();
    }

	/**
	 * Public interface for protected method calculateRecordOffset()
	 * @param int $record_id
	 * @param array $data
	 * @return ?int
	 */
	public function publicCalculateRecordPositionOnPage(int $record_id, array $data): ?int
	{
		return $this->calculateRecordPositionOnPage($record_id, $data);
	}

	public function publicListingsDataContainsNeighborIds(array $data, int $page_position): bool
	{
		return $this->listingsDataContainsNeighborIds($data, $page_position);
	}

	/**
	 * @param int $record_id
	 * @param int $page_position
	 * @return void
	 * @throws Exception
	 */
	public function publicSetOutOfBoundNeighborIds(int $record_id, int $page_position)
	{
		$this->setOutOfBoundNeighborIds($record_id, $page_position);
	}
}
<?php
namespace LittledTests\TestHarness\Filters;

use Littled\Filters\BooleanContentFilter;
use Littled\Filters\DateContentFilter;
use Littled\Filters\IntegerContentFilter;
use Littled\Filters\StringContentFilter;
use LittledTests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;


class TestTableFilters extends FilterCollectionChild
{
    /** @var int */
    protected static int $default_listings_length = 20;
    protected static ?int $content_type_id = TestTableSerializedContentTestHarness::CONTENT_TYPE_ID;

	public StringContentFilter $name;
	public IntegerContentFilter $int_filter;
	public BooleanContentFilter $bool_filter;
	public DateContentFilter $date_after;
	public DateContentFilter $date_before;

	public function __construct()
	{
		parent::__construct();
		$this->name = new StringContentFilter('Name filter', 'name', '', 50);
		$this->int_filter = new IntegerContentFilter('Integer filter', 'intFilter');
		$this->bool_filter = new BooleanContentFilter('Boolean filter', 'boolFilter');
		$this->date_after = new DateContentFilter('Date after', 'dateAfter');
		$this->date_before = new DateContentFilter('Date before', 'dateBefore');
	}

	/**
	 * @inheritDoc
	 */
	protected function formatListingsQuery(bool $calculate_offset=true): array
	{
		parent::formatListingsQuery($calculate_offset);
		return array(
			'CALL testTableListingsSelect (?,?,?,?,?,?,?,@total_matches)',
			'iisiiss',
			&$this->listings_offset,
			&$this->listings_length->value,
			&$this->name_filter->value,
			&$this->int_filter->value,
			&$this->bool_filter->value,
			&$this->date_after->value,
			&$this->date_before->value);
	}

	protected function formatKeywordSearchQuery(): array
	{
		return array(
			'CALL testTableTitlesSelect (?,?,?,@total_matches)',
			'iis',
			&$this->page->value,
			&$this->listings_length->value,
			&$this->name->value);
	}
}

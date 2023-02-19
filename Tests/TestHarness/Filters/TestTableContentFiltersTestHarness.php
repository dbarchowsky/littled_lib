<?php
namespace Littled\Tests\TestHarness\Filters;

use Littled\Filters\BooleanContentFilter;
use Littled\Filters\ContentFilters;
use Littled\Filters\DateContentFilter;
use Littled\Filters\IntegerContentFilter;
use Littled\Filters\StringContentFilter;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;


class TestTableContentFiltersTestHarness extends ContentFilters
{
    /** @var int */
    protected static int $default_listings_length = 20;
    protected static ?int $content_type_id = TestTableSerializedContentTestHarness::CONTENT_TYPE_ID;

	public StringContentFilter $name_filter;
	public IntegerContentFilter $int_filter;
	public BooleanContentFilter $bool_filter;
	public DateContentFilter $date_after;
	public DateContentFilter $date_before;

	public function __construct()
	{
		parent::__construct();
		$this->name_filter = new StringContentFilter('Name filter', 'name', '', 50);
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

	protected function formatTitleSearchQuery(): array
	{
		return array(
			'CALL testTableTitlesSelect (?,?,?,@total_matches)',
			'iis',
			&$this->page->value,
			&$this->listings_length->value,
			&$this->name_filter->value);
	}
}

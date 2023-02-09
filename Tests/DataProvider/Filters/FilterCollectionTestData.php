<?php

namespace Littled\Tests\DataProvider\Filters;


use Littled\Tests\Filters\FilterCollectionTest;

class FilterCollectionTestData
{
    public int                  $record_id;
    public FilterCollectionTestExpectations $expected;
    public ?int                 $page;
	public ?int                 $listings_length;
	public string               $name_filter='';
    public string               $msg        = '';

    function __construct()
    {
        $this->expected = new FilterCollectionTestExpectations();
    }

	public function mapCalculateRecordPositionOnPageTestData(): array
	{
		return array($this->expected->offset, $this->record_id, $this->page, $this->listings_length, $this->name_filter);
	}

	public function mapRetrieveNeighborIdsTestData(): array
	{
		return array(
			$this->expected,
			$this->record_id,
			$this->page,
			$this->listings_length,
			$this->name_filter,
			$this->msg
		);
	}

	public static function newInstance(): FilterCollectionTestData
	{
		return new FilterCollectionTestData();
	}

	public function setRetrieveNeighborIdsTestData(int $record_id, ?int $page, ?int $listings_length, string $name_filter='', string $msg=''): FilterCollectionTestData
	{
		$this->record_id = $record_id;
		$this->page = $page;
		$this->listings_length = $listings_length;
		$this->name_filter = $name_filter;
		$this->msg = $msg;
		return $this;
	}

	public function setExpectations(
		?int $offset=null,
		int $total_records=0,
		?int $previous_record_id=null,
		?int $next_record_id=null
	): FilterCollectionTestData
	{
		$this->expected->setValues($offset, $total_records, $previous_record_id, $next_record_id);
		return $this;
	}
}
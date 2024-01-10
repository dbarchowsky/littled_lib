<?php
namespace LittledTests\DataProvider\Filters\FilterCollection;

class FilterCollectionTestExpectations
{
    public ?int $offset;
    public int $total_records;
    public ?int $previous_record_id;
    public ?int $next_record_id;

    function __construct(
		?int $offset=null,
		int $total_records=0,
		?int $previous_record_id=null,
		?int $next_record_id=null )
    {
		$this->setValues($offset, $total_records, $previous_record_id, $next_record_id);
    }

	public function setValues(
		?int $offset=null,
		int $total_records=0,
		?int $previous_record_id=null,
		?int $next_record_id=null )
	{
		$this->offset = $offset;
		$this->total_records = $total_records;
		$this->previous_record_id = $previous_record_id;
		$this->next_record_id = $next_record_id;
	}
}
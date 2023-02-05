<?php
namespace Littled\Tests\DataProvider\Filters;

class RetrieveNeighborIdsExpectations
{
    public int $offset;
    public int $total_records;
    public ?int $previous_record_id;
    public ?int $next_record_id;

    function __construct(int $offset, int $total_records, ?int $previous_record_id, ?int $next_record_id)
    {
        $this->offset = $offset;
        $this->total_records = $total_records;
        $this->previous_record_id = $previous_record_id;
        $this->next_record_id = $next_record_id;
    }
}
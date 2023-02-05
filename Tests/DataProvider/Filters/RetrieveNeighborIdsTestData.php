<?php

namespace Littled\Tests\DataProvider\Filters;

use Littled\Tests\Filters\TestHarness\TestTableFilters;

class RetrieveNeighborIdsTestData
{
    public int                  $record_id;
    public RetrieveNeighborIdsExpectations $expected;
    public TestTableFilters     $filters;
    public string               $msg        = '';

    function __construct(
        ?int    $expected_offset,
        int     $expected_total_records,
        ?int    $expected_previous_record_id,
        ?int    $expected_next_record_id,
        int     $record_id,
        ?int    $page                               =null,
        ?int    $listings_length                    =null,
        ?string $name_filter                        =null,
        string  $msg='')
    {
        $this->expected = new RetrieveNeighborIdsExpectations(
            $expected_offset,
            $expected_total_records,
            $expected_previous_record_id,
            $expected_next_record_id);
        $this->record_id = $record_id;
        $this->msg = $msg;
        $this->filters = new TestTableFilters();
        $this->filters->page->value = $page;
        $this->filters->listings_length->value = $listings_length;
        $this->filters->name_filter->value = $name_filter;
    }
}
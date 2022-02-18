<?php

namespace Littled\Tests\Filters\TestHarness;

class FilterCollectionChildWithProcedure extends FilterCollectionChild
{
    public function formatListingsQuery(): array
    {
        return array(
            'CALL testTableListingsSelect(?,?,?,?,?,?,?,@total_matches)',
            'iisiiss',
            &$this->page->value,
            &$this->listings_length->value,
            &$this->name_filter->value,
            &$this->int_filter->value,
            &$this->bool_filter->value,
            &$this->date_after->value,
            &$this->date_before->value);
    }
}
<?php

namespace Littled\Tests\TestHarness\Filters;

class FilterCollectionChildWithProcedure extends FilterCollectionChild
{
	/**
	 * @inheritDoc
	 */
    public function formatListingsQuery(bool $calculate_offset=true): array
    {
		parent::formatListingsQuery($calculate_offset);
        return array(
            'CALL testTableListingsSelect(?,?,?,?,?,?,?,@total_matches)',
            'iisiiss',
            &$this->listings_offset,
            &$this->listings_length->value,
            &$this->name_filter->value,
            &$this->int_filter->value,
            &$this->bool_filter->value,
            &$this->date_after->value,
            &$this->date_before->value);
    }
}
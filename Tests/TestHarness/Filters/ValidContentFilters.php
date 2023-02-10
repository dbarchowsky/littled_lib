<?php
namespace Littled\Tests\TestHarness\Filters;

use Littled\Filters\ContentFilters;


class ValidContentFilters extends ContentFilters
{
    protected const VALID_CONTENT_TYPE_ID = 2;

    public static function CONTENT_TYPE_ID(): int { return self::VALID_CONTENT_TYPE_ID; }

	/**
	 * @@inheritDoc
	 */
    protected function formatListingsQuery(bool $calculate_offset=true): array
    {
		parent::formatListingsQuery($calculate_offset);
		$query = "CALL shippingRatesListings(1, 10, '', @total_matches);".
			"SELECT CAST(@total_matches AS UNSIGNED) as `total_matches`;";
        return array($query, '');
    }
}
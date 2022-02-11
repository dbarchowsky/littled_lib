<?php
namespace Littled\Tests\Filters\Samples;

use Littled\Filters\ContentFilters;

class ValidContentFilters extends ContentFilters
{
    protected const VALID_CONTENT_TYPE_ID = 2;

    public static function CONTENT_TYPE_ID(): int { return self::VALID_CONTENT_TYPE_ID; }

    protected function formatListingsQuery(): string
    {
        return "CALL shippingRatesListings(1, 10, '', @total_matches);".
            "SELECT CAST(@total_matches AS UNSIGNED) as `total_matches`;";
    }
}
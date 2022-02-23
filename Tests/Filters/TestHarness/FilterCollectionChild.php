<?php

namespace Littled\Tests\Filters\TestHarness;

use Littled\Filters\BooleanContentFilter;
use Littled\Filters\DateContentFilter;
use Littled\Filters\FilterCollection;
use Littled\Filters\IntegerContentFilter;
use Littled\Filters\StringContentFilter;

class FilterCollectionChild extends FilterCollection
{
    public StringContentFilter $name_filter;
    public IntegerContentFilter $int_filter;
    public BooleanContentFilter $bool_filter;
    public DateContentFilter $date_after;
    public DateContentFilter $date_before;
    protected static string $table_name='test_table';
    protected static int $default_listings_length = 15;

    public function __construct()
    {
        parent::__construct();
        $this->name_filter = new StringContentFilter('name', 'nameFilter', '', 50, $this::getCookieKey());
        $this->int_filter = new IntegerContentFilter('integer column', 'intFilter', null, null, $this::getCookieKey());
        $this->bool_filter = new BooleanContentFilter('boolean column', 'boolFilter', null, null, $this::getCookieKey());
        $this->date_after = new DateContentFilter('date after', 'dateAfter', '', null, $this::getCookieKey());
        $this->date_before = new DateContentFilter('date before', 'dateBefore', '', null, $this::getCookieKey());
    }

    /**
     * @return array
     */
    public function formatListingsQueryTest(): array
    {
        return $this->formatListingsQuery();
    }
}
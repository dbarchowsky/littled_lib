<?php

namespace Littled\Tests\Filters\TestHarness;

use Littled\Filters\BooleanContentFilter;
use Littled\Filters\DateContentFilter;
use Littled\Filters\FilterCollection;
use Littled\Filters\IntegerContentFilter;
use Littled\Filters\StringContentFilter;

class FilterCollectionChild extends FilterCollection
{
    /** @var StringContentFilter */
    public $name_filter;
    /** @var IntegerContentFilter */
    public $int_filter;
    /** @var BooleanContentFilter */
    public $bool_filter;
    /** @var DateContentFilter */
    public $date_after;
    /** @var DateContentFilter */
    public $date_before;
    /** @var string */
    protected static $table_name='test_table';
    /** @var int */
    protected static $default_listings_length = 15;

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
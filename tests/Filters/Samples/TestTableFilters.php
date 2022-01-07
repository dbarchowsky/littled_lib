<?php

namespace Littled\Tests\Filters\Samples;

use Littled\Exception\NotImplementedException;
use Littled\Filters\BooleanContentFilter;
use Littled\Filters\DateContentFilter;
use Littled\Filters\FilterCollection;
use Littled\Filters\IntegerContentFilter;
use Littled\Filters\StringContentFilter;
use Littled\Tests\Filters\BooleanContentFilterTest;

class TestTableFilters extends FilterCollection
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

    public static function DEFAULT_KEY_PREFIX(): string { return ''; }
    public static function DEFAULT_COOKIE_KEY(): string { return ''; }
    public static function DEFAULT_LISTINGS_LENGTH(): int { return 15; }

    public function __construct()
    {
        parent::__construct();
        $this->name_filter = new StringContentFilter('name', 'nameFilter', '', 50, $this::DEFAULT_COOKIE_KEY());
        $this->int_filter = new IntegerContentFilter('integer column', 'intFilter', null, null, $this::DEFAULT_COOKIE_KEY());
        $this->bool_filter = new BooleanContentFilter('boolean column', 'boolFilter', null, null, $this::DEFAULT_COOKIE_KEY());
        $this->date_after = new DateContentFilter('date after', 'dateAfter', '', null, $this::DEFAULT_COOKIE_KEY());
        $this->date_before = new DateContentFilter('date before', 'dateBefore', '', null, $this::DEFAULT_COOKIE_KEY());
    }

    /**
     * @return array
     */
    public function formatListingsQueryTest(): array
    {
        return $this->formatListingsQuery();
    }
}
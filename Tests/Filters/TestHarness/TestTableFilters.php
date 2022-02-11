<?php

namespace Littled\Tests\Filters\TestHarness;

use Littled\Filters\ContentFilters;
use Littled\Tests\PageContent\Serialized\TestHarness\TestTable;

class TestTableFilters extends ContentFilters
{
    /** @var int */
    protected static $default_listings_length = 20;
    protected static $content_type_id = TestTable::CONTENT_TYPE_ID;
}
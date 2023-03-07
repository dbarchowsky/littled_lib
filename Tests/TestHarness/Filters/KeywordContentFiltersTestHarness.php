<?php
namespace Littled\Tests\TestHarness\Filters;

use Littled\Filters\KeywordContentFilters;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;


class KeywordContentFiltersTestHarness extends KeywordContentFilters
{
    protected static ?int $content_type_id = TestTableSerializedContentTestHarness::CONTENT_TYPE_ID;
    protected static int $default_listings_length = 25;
}
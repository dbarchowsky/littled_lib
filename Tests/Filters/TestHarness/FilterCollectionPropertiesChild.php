<?php

namespace Littled\Tests\Filters\TestHarness;

use Littled\Filters\FilterCollectionProperties;

class FilterCollectionPropertiesChild extends FilterCollectionProperties
{
    protected static string $cookie_key = '';
    protected static int $default_listings_length = 50;
    protected static string $listings_label = 'child';
    protected static string $key_prefix = '';
    protected static string $table_name = 'child_table';
}
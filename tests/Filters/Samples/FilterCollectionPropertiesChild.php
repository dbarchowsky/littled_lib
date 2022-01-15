<?php

namespace Littled\Tests\Filters\Samples;

use Littled\Filters\FilterCollectionProperties;

class FilterCollectionPropertiesChild extends FilterCollectionProperties
{
    /** @var string */
    protected static $cookie_key = '';
    /** @var int */
    protected static $default_listings_length = 50;
    /** @var string */
    protected static $listings_label = 'child';
    /** @var string */
    protected static $key_prefix = '';
    /** @var string */
    protected static $table_name = 'child_table';
}
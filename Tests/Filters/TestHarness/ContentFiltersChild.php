<?php
namespace Littled\Tests\Filters\Samples;

use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilters;

class ContentFiltersChild extends ContentFilters
{
    /** @var int */
    protected static ?int $content_type_id = 1; /* articles */
    /** @var string */
    protected static $table_name='article';
    /** @var int */
    protected static $default_listings_length = 20;
    /** @var string */
    protected static $key_prefix = '';
    /** @var string */
    protected static $cookie_key = '';

    /**
     * @return string[]
     * @throws NotImplementedException
     */
    protected function formatListingsQuery(): array
    {
        return array("SELECT id, title, text, author, source, source_url, ".
            "`date`, caption, slot, enabled, keywords ".
            "FROM `".$this::getTableName()."` ".
            "ORDER BY `date` DESC ".
            "LIMIT ".$this->listings_length->value);
    }

    /**
     * @return string
     */
    protected function formatQueryClause(): string
    {
        return '';
    }
}
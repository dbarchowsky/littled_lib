<?php
namespace Littled\Tests\Filters\TestHarness;

use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilters;

class ContentFiltersChild extends ContentFilters
{
    protected static ?int $content_type_id = 1; /* articles */
    protected static string $table_name='article';
    protected static int $default_listings_length = 20;
    protected static string $key_prefix = '';
    protected static string $cookie_key = '';

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
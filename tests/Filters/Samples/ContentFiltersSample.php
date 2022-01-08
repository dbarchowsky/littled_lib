<?php
namespace Littled\Tests\Filters\Samples;

use Littled\Filters\ContentFilters;

class ContentFiltersSample extends ContentFilters
{
    /** @var int */
    public const CONTENT_ID = 1; /* articles */
    /** @var string */
    protected static $table_name='article';
    protected static function DEFAULT_LISTINGS_LENGTH(): int { return 4; }
    protected static function DEFAULT_KEY_PREFIX(): string { return ''; }
    protected static function DEFAULT_COOKIE_KEY(): string { return ''; }

    public function __construct(int $content_type_id)
    {
        $this->content_type_id = self::CONTENT_ID;
        parent::__construct($content_type_id);

        $this->setDefaultListingsLength(20);
        $this->listings_length->value = $this->getDefaultListingsLength();
    }

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
<?php
namespace Littled\Tests\Filters\Samples;

use Littled\Filters\ContentFilterCollection;
use mysqli_result;
use Exception;

class ContentFilterCollectionSample extends ContentFilterCollection
{
    /** @var int */
    public const CONTENT_ID = 1; /* articles */
    protected static function DEFAULT_LISTINGS_LENGTH(): int { return 4; }
    protected static function DEFAULT_KEY_PREFIX(): string
    {
        return 'test';
    }
    protected static function DEFAULT_COOKIE_KEY(): string
    {
        return 'cfcs';
    }
    public static function TABLE_NAME(): string
    {
        return 'article';
    }

    public function __construct(int $content_type_id)
    {
        $this->content_type_id = self::CONTENT_ID;
        parent::__construct($content_type_id);

        $this->setDefaultListingsLength(20);
        $this->listings_length->value = $this->getDefaultListingsLength();
    }

    public function formatListingsQuery(): string
    {
        return "SELECT id, title, text, author, source, source_url, ".
            "`date`, caption, slot, enabled, keywords ".
            "FROM `".$this::TABLE_NAME()."` ".
            "ORDER BY `date` DESC ".
            "LIMIT ".$this->listings_length->value;
    }

    /**
     * @param string $query
     * @return mysqli_result
     * @throws Exception
     */
    public function retrieveListingsUsingProcedureTest(string $query): mysqli_result
    {
        return $this->retrieveListingsUsingProcedure($query);
    }
}
<?php

namespace Littled\Filters;

use Littled\App\LittledGlobals;
use Littled\Database\AppContentBase;
use Littled\Exception\NotImplementedException;


class FilterCollectionProperties extends AppContentBase
{
    const PAGE_KEY = 'p';
    const LISTINGS_LENGTH_KEY = 'pl';
    const NEXT_OPERATION_KEY = 'next';
    const FILTER_KEY = LittledGlobals::FILTER_KEY;
    const LINKS_OFFSET = 5;
    const LINKS_END_LENGTH = 2;
    /** @var BooleanContentFilter   Flag to suppress the display of the listings. */
    public BooleanContentFilter $display_listings;
    /** @var StringContentFilter    Token indicating the next operation to take, typically after editing a record. */
    public StringContentFilter $next;
    /** @var ?int                   Record id of the next record in the sequence matching the current filter values. */
    public ?int $next_record_id = null;
    /** @var IntegerContentFilter   Current page number. */
    public IntegerContentFilter $page;
    /** @var integer                Total number of pages available for records matching the current filter values. */
    public int $page_count;
    /** @var IntegerContentFilter   Maximum number of records to display per page. */
    public IntegerContentFilter $listings_length;
    /** @var ?int                   Record id of the previous record in the sequence matching the current filter values. */
    public ?int $previous_record_id = null;
    /** @var string                 SQL query string used to fetch the current record set. */
    public string $query_string = '';
    /** @var integer                Total number of records matching the current filter values. */
    public int $record_count = 0;
    /** @var string                 URL to redirect back to, if specified */
    public string $referer_uri = '';
    /** @var string                 SQL WHERE clause matching the current filter values. */
    public string $sql_clause = '';
    /** @var string                 Key for cookie used to preserve filter settings. */
    protected static string $cookie_key = '';
    /** @var int                    Default number of line items to display in listings */
    protected static int $default_listings_length;
    /** @var string                 Item label to insert into listings content. */
    protected static string $listings_label = '';
    /** @var string                 String to add to parameter names to make them specific to the current type of listings. */
    protected static string $key_prefix = '';
    /** @var string                 Name of table storing listings content. */
    protected static string $table_name = '';

    /**
     * class constructor
     * @throws NotImplementedException
     */
    function __construct()
    {
        parent::__construct();
        $this->page = new IntegerContentFilter("Page", $this::PAGE_KEY, null, null, $this::getCookieKey());
        $this->listings_length = new IntegerContentFilter("Page length", $this::LISTINGS_LENGTH_KEY, $this::getDefaultListingsLength(), null, $this::getCookieKey());
        $this->next = new StringContentFilter("Next", $this::NEXT_OPERATION_KEY, '', 16, $this::getCookieKey());
        $this->display_listings = new BooleanContentFilter("Display listings", $this::FILTER_KEY, false, null, $this::getCookieKey());
        $this->referer_uri = '';
    }

    /**
     * Abstract method for cookie key getter. Child classes will set the default value of the cookie key in their
     * implementations of the method.
     * @return string
     */
    public static function getCookieKey(): string
    {
        return static::$cookie_key;
    }

    /**
     * Abstract method for default listings length getter. Child classes will set an initial value for the property in
     * their implementations of the method.
     * @throws NotImplementedException
     */
    public static function getListingsLabel(): string
    {
        if (!static::$listings_label) {
            throw new NotImplementedException('Listings label value not set in ' . get_called_class() . '.');
        }
        return static::$listings_label;
    }

    /**
     * Default listings length getter. Child classes will set an initial value for the property in
     * their implementations of the method.
     * @returns int
     * @throws NotImplementedException
     */
    public static function getDefaultListingsLength(): int
    {
        if (!static::$default_listings_length) {
            throw new NotImplementedException('Default listings length value not set in ' . get_called_class() . '.');
        }
        return static::$default_listings_length;
    }

    /**
     * Abstract method for default query string variable name prefix. Child classes will set a default value for the
     * property in their implementation of the method.
     * @return string
     */
    public static function getKeyPrefix(): string
    {
        return static::$key_prefix;
    }

    /**
     * Returns a localized name for a query string variable that will hold the value of one of the filters.
     * @param string $base_key Base name of the variable to be added to a localized prefix.
     * @return string
     */
    public static function getLocalKey(string $base_key): string
    {
        return (static::getKeyPrefix() . $base_key);
    }

    /**
     * Returns the halfway point in the sequence of page numbers displayed in listings page navigation.
     * @return int
     */
    public static function getPageListHalfPoint(): int
    {
        return FilterCollectionProperties::LINKS_OFFSET + FilterCollectionProperties::LINKS_END_LENGTH + 1;
    }

    /**
     * When content listings consist of many pages, the listings can be displayed with ellipses. This method returns
     * page number in the sequence of pages where that break should begin.
     * @return int
     */
    public static function getPageListCollapsePoint(): int
    {
        return (int)((FilterCollectionProperties::LINKS_OFFSET * 2) + (FilterCollectionProperties::LINKS_END_LENGTH * 2) + 1);
    }

    /**
     * Abstract method for table name getter. Child classes will set initial value within the method.
     * @return string
     * @throws NotImplementedException
     */
    public static function getTableName(): string
    {
        if (!static::$table_name) {
            throw new NotImplementedException('Table name not set in ' . __CLASS__ . '.');
        }
        return static::$table_name;
    }

    /**
     * Setter for key used to preserve filter values in cookie data.
     * @param string $key
     */
    public static function setCookieKey(string $key): void
    {
        static::$cookie_key = $key;
    }

    /**
     * Setter for listings label property
     * @param string $label
     */
    public static function setListingsLabel(string $label): void
    {
        static::$listings_label = $label;
    }

    /**
     * Setter for default listings length property value.
     * @param int $length
     */
    public static function setDefaultListingsLength(int $length): void
    {
        static::$default_listings_length = $length;
    }

    /**
     * Key prefix setter.
     * @param $prefix
     */
    public static function setKeyPrefix($prefix): void
    {
        static::$key_prefix = $prefix;
    }

    /**
     * Setter for name of table containing listing content.
     * @param string $table
     */
    public static function setTableName(string $table): void
    {
        static::$table_name = $table;
    }

    /**
     * @deprecated Use get/setCookieKey() instead
     */
    protected static function DEFAULT_COOKIE_KEY(): string
    {
        return '';
    }

    /**
     * @deprecated Use get/setKeyPrefix() instead.
     */
    protected static function DEFAULT_KEY_PREFIX(): string
    {
        return '';
    }

    /**
     * @deprecated Use get/setListingsLabel() instead
     */
    protected static function DEFAULT_LISTINGS_LABEL(): string
    {
        return '';
    }

    /**
     * @deprecated Use $default_listings_length and get/setListingsLength() instead
     */
    protected static function DEFAULT_LISTINGS_LENGTH(): ?int
    {
        return null;
    }

    /**
     * @deprecated Use $getTableName instead
     */
    protected static function DEFAULT_TABLE_NAME(): string
    {
        return '';
    }
}

<?php

namespace Littled\Filters;


use Littled\Exception\NotImplementedException;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;

/**
 * Class GalleryPaging
 * @package Littled\Filters
 */
class GalleryPaging extends FilterCollection
{
    /** @var IntegerContentFilter Book filter */
    public IntegerContentFilter $book_id;
    /** @var IntegerContentFilter Page filter */
    public IntegerContentFilter $page_id;
    /** @var IntegerContentFilter Menu page filter */
    public IntegerContentFilter $menu_page;
    /** @var StringContentFilter Referring URL. */
    public StringContentFilter $ref;
    /** @var int Content type id. */
    public int $contentTypeID;
    /** @var int Page content type id. */
    public int $pageContentTypeID;
    /** @var int Next record id in the sequence of pages. */
    public int $nextRecordID;
    /** @var int Previous record id in the sequence of pages. */
    public int $previousRecordID;
    /** @var string Cookie key */
    public const COOKIE_NAME = "cmc";
    /** @var string Book filter variable name. */
    public const BOOK_PARAM = "b";
    /** @var string Page filter variable name. */
    public const PAGE_KEY = "p";
    /** @var string Menu filter variable name. */
    public const MENU_PARAM = "m";
    /** @var int Number of records to display in front-end listings. */
    public static int $frontend_page_length = 8;
    /** @var int Content type id as defined in site_section table to be defined in derived classes. */
    public static int $content_type_id;
    /** @var int Page content type id as defined in site_section table to be defined in derived classes. */
    public static int $page_content_type_id;
    /** @var string Label to be used to describe records in listings content. */
    public static string $listings_label = "";

    public function DEFAULT_PAGE_LEN(): int
    {
        return static::$frontend_page_length;
    }

    /**
     * GalleryPaging constructor.
     * @param int $content_type_id
     * @param int $page_content_type_id
     * @throws NotImplementedException
     */
    function __construct(int $content_type_id, int $page_content_type_id)
    {
        parent::__construct();
        $this->contentTypeID = $content_type_id;
        $this->pageContentTypeID = $page_content_type_id;
        $this->book_id = new IntegerContentFilter("book", $this::BOOK_PARAM, null, null, $this::COOKIE_NAME);
        $this->page_id = new IntegerContentFilter("page", $this::PAGE_KEY, null, null, $this::COOKIE_NAME);
        $this->menu_page = new IntegerContentFilter("menu", $this::MENU_PARAM, null, null, $this::COOKIE_NAME);
        $this->ref = new StringContentFilter("referer", "ref", '', 200, $this::COOKIE_NAME);
    }

    /**
     * Returns object containing parameters for the current page to be displayed on the front-end.
     * Must be defined in derived classes.
     * @throws NotImplementedException
     */
    public function retrievePage()
    {
        throw new NotImplementedException("retrievePage() not implemented in inherited class.");
    }

    /**
     * Format SQL WHERE clause that will filter down the listings.
     * @param array|null $exclude Array containing the names of parameters that should not be included in the query string.
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function formatQueryString(array|null $exclude = null): string
    {
        /* get the requested book. make sure it belongs in this section */
        $this->connectToDatabase();
        if ($this->page_id->value > 0) {
            /* get a requested page. make sure it's of the correct type */
            $this->sql_clause .=
                "AND (p.id = {$this->page_id->value}) " .
                '"AND (p.type_id = ' . static::$page_content_type_id . ')';
        } else {
            /* no page specified - get the first page */
            $this->sql_clause .= "ORDER BY IFNULL(p.page_number,999999) ASC, IFNULL(p.slot,999999) ASC LIMIT 1";
        }
        $this->sql_clause =
            "WHERE (b.id = {$this->book_id->value}) " .
            'AND (b.section_id = ' . static::$content_type_id . ') ' .
            "AND (b.access='public') AND (p.access='public') " .
            "AND (p.release_date IS NOT NULL) AND (DATEDIFF(p.release_date,NOW())<=0) ";
        return $this->sql_clause;
    }

    /**
     * Sets values of internal properties of the object to the number of records and pages in the current set of listings.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    public function getPageCount(): void
    {
        /* get total number of pages */
        $query = <<<SQL
SELECT COUNT(*) AS `count` 
FROM image_link p 
INNER JOIN `album` b ON (p.parent_id = b.id AND p.type_id = {$this::$page_content_type_id}) 
WHERE (b.id={$this->book_id->value}) 
AND (b.access='public') AND (p.access='public') 
AND (p.release_date IS NOT NULL) AND (DATEDIFF(p.release_date,NOW())<=0) 
SQL;
        $this->record_count = $this->fetchRecords($query)[0]->count;
    }

    /**
     * Retrieves recordset containing comic book records for listings page and for
     * comics menu displayed under the individual comics viewer.
     * @param int $lower_limit Lower limit of the records to return. Defaults to 0.
     * @param int $upper_limit Upper limit of the records to return. Defaults to 99999999999.
     * @return array Comic book dataset.
     * @throws InvalidQueryException
     */
    public function getMenuRecords(int $lower_limit = 0, int $upper_limit = 99999999999): array
    {
        $this->getMenuRecordCount();

        $date = date("Y-m-d");
        $query = <<<SQL
SELECT 
	c.id, 
	c.title, 
	c.slug,
	tn.path, 
	tn.width, 
	tn.height, 
	c.description, 
	DATE_FORMAT(c.release_date,'%M %e, %Y') display_date 
FROM `album` c 
INNER JOIN 
(
	image_link il INNER JOIN images tn ON il.fullres_id = tn.id
) ON c.tn_id = il.id 
WHERE (c.section_id = {$this->contentTypeID}) 
AND (c.access = 'public') 
AND (DATEDIFF(c.release_date, '{$date}')<=0) 
ORDER BY IFNULL(c.slot,999999), c.release_date DESC, c.id DESC 
LIMIT {$lower_limit}, {$upper_limit}
SQL;
        return ($this->fetchRecords($query));
    }

    /**
     * Parse query string to get book and page properties.
     * @param bool $save_filters If set to TRUE, save all filter values in session variables. Default value is TRUE.
     * @return void
     * @throws NotImplementedException
     */
    public function collectFilterValues(bool $save_filters = true): void
    {
        parent::collectFilterValues($save_filters);
        if (!isset($this->menu_page->value)) {
            $this->menu_page->value = 1;
        }
        if (!isset($this->page->value)) {
            $this->page->value = 1;
        }
        if (!isset($this->listings_length->value)) {
            $this->listings_length->value = $this::$frontend_page_length;
        }
        if ($this->next->value == "") {
            $this->next->value = "view";
        }
    }

    /**
     * Returns a URI to the next page in the gallery sequence.
     * @param string $direction "prev" or "next", depending on which direction to look for the next record.
     * @throws NotImplementedException
     */
    protected function getNeighborURI(string $direction)
    {
        throw new NotImplementedException("_getNeighborURI('{$direction}') not implemented in inherited class.");
    }

    /**
     * Returns a URI to the next page in the gallery sequence.
     * @throws NotImplementedException
     */
    protected function nextPageURI()
    {
        return ($this->getNeighborURI("next"));
    }

    /**
     * Returns a URI to the previous page in the gallery sequence.
     * @throws NotImplementedException
     */
    protected function previousPageURI()
    {
        return ($this->getNeighborURI("prev"));
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    protected function getMenuRecordCount(): void
    {
        $date = date("Y-m-d");
        $query = <<<SQL
SELECT COUNT(*) AS `count`  
FROM `album` c 
INNER JOIN 
(
	image_link il INNER JOIN images tn ON il.fullres_id = tn.id
) ON c.tn_id = il.id 
WHERE (c.section_id = {$this->contentTypeID}) 
AND (c.access = 'public') 
AND (DATEDIFF(c.release_date, '{$date}')<=0) 
SQL;
        $this->record_count = $this->fetchRecords($query)[0]->count;
    }
}
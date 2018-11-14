<?php
namespace Littled\Filters;


use Littled\Exception\NotImplementedException;

/**
 * Class GalleryPaging
 * @package Littled\Filters
 */
class GalleryPaging extends FilterCollection
{
	/** @var IntegerContentFilter Book filter */
	public $book_id;
	/** @var IntegerContentFilter Page filter */
	public $page_id;
	/** @var IntegerContentFilter Menu page filter */
	public $menu_page;
	/** @var StringContentFilter Referring URL. */
	public $ref;

	/** @var int Content type id. */
	public $contentTypeID;
	/** @var int Page content type id. */
	public $pageContentTypeID;
	/** @var int Next record id in the sequence of pages. */
	public $nextRecordID;
	/** @var int Previous record id in the sequence of pages. */
	public $previousRecordID;

	/** @var string Cookie key */
	const COOKIE_NAME = "cmc";
	/** @var string Book filter variable name. */
	const BOOK_PARAM = "b";
	/** @var string Page filter variable name. */
	const PAGE_PARAM = "p";
	/** @var string Menu filter variable name. */
	const MENU_PARAM = "m";
	/** @var int Number of records to display in front-end listings. */
	public static $frontend_page_length = 8;

	/** @var int Content type id as defined in site_section table to be defined in derived classes. */
	public static $content_type_id = null;
	/** @var int Page content type id as defined in site_section table to be defined in derived classes. */
	public static $page_content_type_id = null;
	/** @var string Label to be used to describe records in listings content. */
	public static $listings_label = "";

	public function DEFAULT_PAGE_LEN () { return($this::$frontend_page_length); }

	/**
	 * GalleryPaging constructor.
	 * @param int $content_type_id
	 * @param int $page_content_type_id
	 */
	function __construct ($content_type_id, $page_content_type_id )
	{
		parent::__construct();
		$this->contentTypeID = $content_type_id;
		$this->pageContentTypeID = $page_content_type_id;
		$this->book_id = new IntegerContentFilter("book", $this::BOOK_PARAM, null, null, $this::COOKIE_NAME);
		$this->page_id = new IntegerContentFilter("page", $this::PAGE_PARAM, null, null, $this::COOKIE_NAME);
		$this->menu_page = new IntegerContentFilter("menu", $this::MENU_PARAM, null, null, $this::COOKIE_NAME);
		$this->ref = new StringContentFilter("referer", "ref", '', 200, $this::COOKIE_NAME);
	}

	/**
	 * Returns object containing parameters for the current page to be displayed on the front-end.
	 * Must be defined in derived classes.
	 * @throws NotImplementedException
	 */
	public function retrievePage ()
	{
		throw new NotImplementedException("retrievePage() not implemented in inherited class.");
	}

	/**
	 * Format SQL WHERE clause that will filter down the listings.
	 * @param array|null[optional] $exclude Array containing the names of parameters that should not be included in the query string.
	 * @return string|void
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	public function formatQueryString ( $exclude=null )
	{
		/* get the requested book. make sure it belongs in this section */
		$this->connectToDatabase();
		if ($this->page_id->value>0) {
			/* get a requested page. make sure it's of the correct type */
			$this->sqlClause .=
				"AND (p.id = {$this->page_id->value}) ".
				"AND (p.type_id = {$this::$page_content_type_id}) ";
		}
		else {
			/* no page specified - get the first page */
			$this->sqlClause.= "ORDER BY IFNULL(p.page_number,999999) ASC, IFNULL(p.slot,999999) ASC LIMIT 1";
		}
		$this->sqlClause =
			"WHERE (b.id = {$this->book_id->value}) ".
			"AND (b.section_id = {$this::$content_type_id}) ".
			"AND (b.access='public') AND (p.access='public') ".
			"AND (p.release_date IS NOT NULL) AND (DATEDIFF(p.release_date,NOW())<=0) ";
	}

	/**
	 * Sets values of internal properties of the object to the number of records and pages in the current set of listings.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	public function getPageCount()
	{
		/* get total number of pages */
		$query = <<<SQL
SELECT COUNT(*) AS `count` 
FROM image_link p 
INNER JOIN `album` b ON (p.parent_id = b.id AND p.type_id = ?) 
WHERE (b.id=?) 
AND (b.access='public') AND (p.access='public') 
AND (p.release_date IS NOT NULL) AND (DATEDIFF(p.release_date,NOW())<=0) 
SQL;
		$data = $this->mysqli()->fetchRecords($query, array(
			$this::$page_content_type_id,
			$this->book_id->escapeSQL($this->mysqli)
		));
		$this->recordCount = $data[0]->count;
	}

	/**
	 * Retrieves recordset containing comic book records for listings page and for
	 * comics menu displayed under the individual comics viewer.
	 * @param int[optional] $lower_limit Lower limit of the records to return. Defaults to 0.
	 * @param int[optional] $upper_limit Upper limit of the records to return. Defaults to 99999999999.
	 * @return array Comic books dataset.
	 */
	public function getMenuRecords ( $lower_limit=0, $upper_limit=99999999999 )
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
WHERE (c.section_id = ?) 
AND (c.access = 'public') 
AND (DATEDIFF(c.release_date, ?)<=0) 
ORDER BY IFNULL(c.slot,999999), c.release_date DESC, c.id DESC 
LIMIT ?, ?
SQL;
		return($this->fetchRecords($query, array($this->contentTypeID, $date, $lower_limit, $upper_limit)));
	}

	/**
	 * Parse query string to get book and page properties.
	 * @param bool[optional] $save_filters If set to TRUE, save all filter values in session variables. Default value is TRUE.
	 */
	public function collectFilterValues( $save_filters=true )
	{
		parent::collectFilterValues($save_filters);
		if (!isset($this->menu_page->value)) {
			$this->menu_page->value = 1;
		}
		if (!isset($this->page->value)) {
			$this->page->value = 1;
		}
		if (!isset($this->listingsLength->value)) {
			$this->listingsLength->value = $this::$frontend_page_length;
		}
		if ($this->next->value=="") {
			$this->next->value = "view";
		}
	}

	/**
	 * Returns a URI to the next page in the gallery sequence.
	 * @param string $direction "prev" or "next", depending on which direction to look for the next record.
	 * @throws NotImplementedException
	 */
	protected function getNeighborURI($direction )
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
	 * Gets a count of gallery items that should be displayed with the album.
	 */
	protected function getMenuRecordCount()
	{
		$query = <<<SQL
SELECT COUNT(*) AS `count`  
FROM `album` c 
INNER JOIN 
(
	image_link il INNER JOIN images tn ON il.fullres_id = tn.id
) ON c.tn_id = il.id 
WHERE (c.section_id = ?) 
AND (c.access = 'public') 
AND (DATEDIFF(c.release_date, ?)<=0) 
SQL;
			$data = $this->fetchRecords($query, array($this->contentTypeID, "'".date("Y-m-d")."'"));
			$this->recordCount = $data[0]->count;
	}
}
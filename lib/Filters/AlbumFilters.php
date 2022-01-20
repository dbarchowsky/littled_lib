<?php
namespace Littled\Filters;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\SiteSection\ListingsKeywords;
use Littled\Ajax\ContentAjaxProperties;
use Littled\PageContent\SiteSection\ContentProperties;

/**
 * Class AlbumFilters
 * @package Littled\Filters
 */
class AlbumFilters extends FilterCollection
{
	const RELEASED_AFTER_PARAM = "fara";
	const RELEASED_BEFORE_PARAM = "farb";
	const COOKIE_NAME = "alb";
	const DEFAULT_PAGE_LEN = 20;

	/** @var StringContentFilter Keyword filter. */
	public $keyword;
	/** @var StringContentFilter Album title filter. */
	public $title;
	/** @var StringContentFilter Name filter. */
	public $name;
	/** @var StringContentFilter Display date filter. */
	public $date;
	/** @var StringContentFilter Access level filter. */
	public $access;
	/** @var StringContentFilter Filters out records with release dates before the value of this property. */
	public $releaseAfter;
	/** @var StringContentFilter Filters out records with release dates after the value of this property. */
	public $releaseBefore;
	/** @var IntegerContentFilter Slot filter. */
	public $slot;
	/** @var ContentProperties Content properties. */
	public $contentProperties;
	/** @var ContentAjaxProperties Extended content properties. */
	public $ajaxProperties;
	/** @var GalleryFilters Gallery filters. */
	public $gallery;
	/** @var int $contentTypeID Pointer to contentProperties->id->value for convenience */
	public $contentTypeID;
	public static $frontEndURI = '';

	public static function DEFAULT_PAGE_LEN() { return(self::DEFAULT_PAGE_LEN); }
	public static function FRONTEND_URI() { return(static::$frontEndURI); }

	/**
	 * AlbumFilters constructor
	 * @param int $content_type_id ID of the section of the site containing the listings. (From the site_section table.)
	 * @param int $page_content_type_id ID of the site_section representing the images within the listings (From the site_section table.)
	 * @param int[optional] $default_page_len Length of the pages of listings.
	 * @throws InvalidQueryException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	function __construct ( $content_type_id, $page_content_type_id, $default_page_len=10 )
	{
		parent::__construct();

		$this->keyword = new StringContentFilter("keyword", Keyword::FILTER_PARAM, '', 50, $this::COOKIE_NAME);
		$this->title = new StringContentFilter("title", "fati", '', 50, $this::COOKIE_NAME);
		$this->name = &$this->title;
		$this->date = new StringContentFilter("date", "fadt", '', 20, $this::COOKIE_NAME);
		$this->access = new StringContentFilter("access", "faac", '', 20, $this::COOKIE_NAME);
		$this->releaseAfter = new DateContentFilter("released after", $this::RELEASED_AFTER_PARAM, '', 20, $this::COOKIE_NAME);
		$this->releaseBefore = new DateContentFilter("released before", $this::RELEASED_BEFORE_PARAM, '', 20, self::COOKIE_NAME);
		$this->slot = new IntegerContentFilter("slot", "fasl", null, null, self::COOKIE_NAME);

		$this->contentProperties = new ContentProperties($content_type_id);
		$this->contentTypeID = &$this->contentProperties->id->value;
		$this->ajaxProperties = new ContentAjaxProperties();
		$this->ajaxProperties->section_id->value = $content_type_id;
		$this->getAjaxProperties();
		$this->gallery = new GalleryFilters($page_content_type_id, $default_page_len);
		$this->previous_record_id = $this->next_record_id = 0;
	}

	/**
	 * Get filter values from query string and/or form data.
	 * @param bool[optional] $save_filters
	 * @return void
	 */
	public function collectFilterValues ($save_filters=true )
	{
		parent::collectFilterValues($save_filters);
		if (!isset($this->page->value)) {
			$this->page->value = 1;
		}
		if (!isset($this->listings_length->value)) {
			$this->listings_length->value = $this->DEFAULT_PAGE_LEN();
		}
		if ($this->next->value=="") {
			$this->next->value = "view";
		}
		$this->gallery->collectFilterValues($save_filters);
	}

	/**
	 * Returns SQL to retrieve album listings.
	 * @return string SQL string used to retrieve album listings
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	protected function formatListingsQuery()
	{
		$this->connectToDatabase();
		$this->query_string = "CALL albumFilteredListingsSelect(".
			$this->page->escapeSQL($this->mysqli).",".
			$this->listings_length->escapeSQL($this->mysqli).",".
			"NULL,".
			$this->contentProperties->id->escapeSQL($this->mysqli).",".
			$this->gallery->content_properties->id->escapeSQL($this->mysqli).",".
			$this->title->escapeSQL($this->mysqli).",".
			$this->date->escapeSQL($this->mysqli).",".
			$this->releaseAfter->escapeSQL($this->mysqli).",".
			$this->releaseBefore->escapeSQL($this->mysqli).",".
			$this->access->escapeSQL($this->mysqli).",".
			$this->slot->escapeSQL($this->mysqli).",".
			$this->keyword->escapeSQL($this->mysqli).",".
			"@total_matches);SELECT @total_matches AS `total_matches`;";
		return ($this->query_string);
	}

	/**
	 * @deprecated Use stored procedure instead.
	 * Returns select portion of SQL statement to retrieve album listings.
	 */
	protected function formatListingsSelectQuery( )
	{
		return ('');
	}

	/**
	 * @deprecated Use stored procedure instead.
	 * Create SQL string containing WHERE clause that will filter down the listings.
	 */
	public function formatQueryClause( )
	{
		return ('');
	}

	/**
	 * Overrides parent function to include image filters.
	 * @param array $exclude List of parameters to exclude from the query string.
	 * @return string Query string containing all filters as parameter/value pairs.
	 */
	public function formatQueryString ($exclude=null )
	{
		parent::formatQueryString($exclude);
		$gqs = $this->gallery->formatQueryString($exclude);
		$this->query_string .= preg_replace("/^\?/", "&", $gqs);
		return ($this->query_string);
	}

	/**
	 * Retrieve section properties.
	 * @param integer $content_type_id Id of site section to retrieve properties for.
	 * @throws InvalidQueryException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function getAjaxProperties ($content_type_id=null)
	{
		if ($content_type_id > 0) {
			$this->contentProperties->id->value = $content_type_id;
			$this->ajaxProperties->section_id->value = $content_type_id;
		}
		if ($this->contentProperties->id->value > 0) {
			$this->contentProperties->read();
			$this->ajaxProperties->retrieveContentProperties();
		}
	}

	/**
	 * Retrieves from database the uri of the page used to display details for this content type.
	 * @return string Uri of details page.
	 * @throws InvalidQueryException
	 */
	public function getDetailsURI()
	{
		if ($this->contentProperties->id->value===null || $this->contentProperties->id->value<1) {
			return ("");
		}

		$query = "SELECT `details_uri` ".
			"FROM `section_operations` ".
			"WHERE `section_id` = {$this->contentProperties->id->value}";
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			return($data[0]->details_uri);
		}
		return ("");
	}

	/**
	 * @deprecated Use stored procedure instead.
	 * Sets values of internal properties of the object to the number of records and pages in the current set of listings.
	 */
	public function getPageCount ()
	{
		return;
	}

	/**
	 * Returns a string containing all filters in the collection expressed as a JavaScript array.
	 * Overrides parent to include image filters in the array.
	 * @param array $exclude Array containing the names of any filters to not add to the string.
	 * @return string String containing JavaScript array.
	 */
	public function jsonEncode($exclude=null )
	{
		$sJS1 = parent::jsonEncode($exclude);
		$sJS2 = $this->gallery->jsonEncode($exclude);
		if ($sJS1 && $sJS2) {
			return($sJS1.", ".$sJS2);
		}
		else {
			return($sJS1.$sJS2);
		}
	}

	/**
	 * Overrides parent function to include image filters.
	 * @param array $exclude List of parameters to exclude from the query string.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 */
	public function preserveInForm($exclude=null )
	{
		parent::preserveInForm($exclude);
		$this->gallery->preserveInForm($exclude);
	}

	/**
	 * Returns an appropriate label given the value of $count if $count requires the label to be pluralized.
	 * @param int|null[optional] $count Number determining if the label is plural or not.
	 * @return string Plural form of the record label if $count is not 1.
	 */
	public function pluralLabel( $count=null )
	{
		if ($count===null) {
			$count = $this->record_count;
		}
		if ($this->ajaxProperties->label->value) {
			return($this->ajaxProperties->pluralLabel($count));
		}
		if ($this->contentProperties->label) {
			return($this->contentProperties->pluralLabel($count));
		}
		return ('');
	}

	/**
	 * Returns context to use to render gallery listings.
	 * @param string $url Base URL to use for links back to this page.
	 * @param string $query_string Query string to use to preserve filter values when linking back to this page.
	 * @return array $context
	 * @throws \Exception
	 */
	public function prepareListingsContext($url, $query_string)
	{
		$context = array(
			'filters' => $this,
			'url' => $url,
			'query_string' => htmlentities($query_string),
			'keywords' => new ListingsKeywords(null, $this->contentTypeID),
			'data' => array());
		if ($this->display_listings->value) {
			$data = $this->retrieveListings();
			foreach ($data as $row) {
				$row->name_widget_data = (object)array("id" => $row->id, "table" => $this->contentProperties->table->value, "value" => $row->title);
				$row->access_widget_data = (object)array("id" => $row->id, "table" => $this->contentProperties->table->value, "value" => $row->access);
				$row->date_widget_data = (object)array("id" => $row->id, "table" => $this->contentProperties->table->value, "value" => $row->release_date);
			}
			$context['data'] = $data;
		}
		return ($context);
	}

	/**
	 * Retrieves recordset containing album titles matching the current filters.
	 * @return array Album titles data set.
	 * @throws InvalidQueryException
	 * @throws ResourceNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Exception
	 */
	public function searchTitles()
	{
		$this->connectToDatabase();
		$query = "CALL albumTitlesSelect(".
			$this->page->escapeSQL($this->mysqli).",".
			$this->listings_length->escapeSQL($this->mysqli).",".
			$this->keyword->escapeSQL($this->mysqli).",".
			$this->contentProperties->id->escapeSQL($this->mysqli).",".
			"@total_matches);SELECT CAST(@total_matches AS UNSIGNED) as `total_matches`";
		$data = $this->fetchRecordsNonExhaustive($query);
		if (!$data) {
			throw new ResourceNotFoundException("Error retrieving album titles.");
		}
		/* get record count from sproc results */
		$this->getProcedurePageCount();
		return ($data);
	}

	/**
	 * Retrieves listings using sql in $query argument. Stores the total
	 * number of matches and updates internal values of total number of pages
	 * and current page number.
	 * @return array List of generic objects containing the records returned by the query.
	 * @throws \Exception Error running query.
	 */
	public function retrieveListings()
	{
		$data = $this->fetchRecordsNonExhaustive($this->formatListingsQuery());
		$this->getProcedurePageCount();
		return ($data);
	}
}
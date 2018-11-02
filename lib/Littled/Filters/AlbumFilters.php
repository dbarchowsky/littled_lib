<?php
namespace Littled\Filters;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\SiteContent\ContentAjaxProperties;
use Littled\SiteContent\ContentProperties;

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
	public $siteSection;
	/** @var ContentAjaxProperties Extended content properties. */
	public $contentProperties;
	/** @var GalleryFilters Gallery filters. */
	public $gallery;

	public static function DEFAULT_PAGE_LEN() { return(self::DEFAULT_PAGE_LEN); }

	/**
	 * AlbumFilters constructor
	 * @param int $content_type_id ID of the section of the site containing the listings. (From the site_section table.)
	 * @param int $page_content_type_id ID of the site_section representing the images within the listings (From the site_section table.)
	 * @param int[optional] $default_page_len Length of the pages of listings.
	 * @throws \Exception
	 */
	function __construct ( $content_type_id, $page_content_type_id, $default_page_len=10 )
	{
		parent::__construct();

		$this->keyword = new StringContentFilter("keyword", Keyword::FILTER_PARAM, 50, "", self::COOKIE_NAME);
		$this->title = new StringContentFilter("title", "fati", 50, "", self::COOKIE_NAME);
		$this->name = &$this->title;
		$this->date = new StringContentFilter("date", "fadt", 20, "", self::COOKIE_NAME);
		$this->access = new StringContentFilter("access", "faac", 20, "", self::COOKIE_NAME);
		$this->releaseAfter = new StringContentFilter("released after", self::RELEASED_AFTER_PARAM, 20, "", self::COOKIE_NAME);
		$this->releaseBefore = new StringContentFilter("released before", self::RELEASED_BEFORE_PARAM, 20, "", self::COOKIE_NAME);
		$this->slot = new IntegerContentFilter("slot", "fasl", null, 0, self::COOKIE_NAME);

		$this->siteSection = new ContentProperties($content_type_id);
		$this->contentProperties = new ContentAjaxProperties();
		$this->contentProperties->section_id->value = $content_type_id;
		$this->getContentProperties();
		$this->gallery = new GalleryFilters($page_content_type_id, $default_page_len);
		$this->previousRecordId = $this->nextRecordId = 0;
	}


	/**
	 * Create SQL string containing WHERE clause that will filter down the listings.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	public function formatQueryClause( )
	{
		$this->connectToDatabase();
		$this->sqlClause = "WHERE (a.section_id = {$this->siteSection->id->value}) ";
		if ($this->gallery->albumId->value>0) {
			$this->sqlClause .= "AND (a.id = {$this->gallery->albumId->value}) ";
		}
		if ($this->title->value) {
			$this->sqlClause .= "AND (a.title LIKE '%".$this->title->escapeSQL($this->mysqli, false)."%') ";
		}
		if ($this->date->value) {
			$this->sqlClause .= "AND (a.`date` LIKE '%".$this->date->escapeSQL($this->mysqli, false)."%') ";
		}
		if ($this->releaseAfter->value) {
			$this->sqlClause .= "AND (DATEDIFF(a.`release_date`,".$this->releaseAfter->escapeSQL($this->mysqli).")>=0) ";
		}
		if ($this->releaseBefore->value) {
			$this->sqlClause .= "AND (DATEDIFF(a.`release_date`,".$this->releaseBefore->escapeSQL($this->mysqli).")<=0) ";
		}
		if ($this->access->value) {
			$this->sqlClause .= "AND (a.`access` = ".$this->access->escapeSQL($this->mysqli).") ";
		}
		if ($this->slot->value) {
			$this->sqlClause .= "AND (a.`slot` = ".$this->slot->escapeSQL($this->mysqli).") ";
		}
		if ($this->keyword->value) {
			$this->sqlClause .= "AND (MATCH(a.title,a.description,a.keywords) AGAINST (".$this->keyword->escapeSQL($this->mysqli)." IN BOOLEAN MODE)) ";
		}
	}

	/**
	 * Sets values of internal properties of the object to the number of records and pages in the current set of listings.
	 */
	public function getPageCount ()
	{
		try
		{
			$this->formatQueryClause();

			$query = "SEL"."ECT COUNT(DISTINCT a.`id`) AS `count` FROM `album` a ".
				"LEFT JOIN `image_link` p ON a.`id` = p.`parent_id` ".
				$this->sqlClause;
			$data = $this->fetchRecords($query);

			$this->recordCount = $data[0]->count;
			$this->calcPageCount();
		}
		catch (\Exception $ex)
		{
			print ("<div class=\"alert alert-error\">Error retrieving image count: ".$ex->getMessage()."</div>");
		}
	}

	/**
	 * Returns select portion of SQL statement to retrieve album listings.
	 * @return string SQL string used to retrieve album listings
	 */
	protected function formatListingsSelectQuery( )
	{
		$query = <<<SQL
SELECT a.id 
	, a.title
	, a.slug
	, a.description
	, a.`date` 
	, a.slot
	, (SELECT COUNT(*) FROM image_link pub WHERE (pub.parent_id = a.id) AND (pub.type_id = {$this->gallery->siteSection->id->value})) private_pages
	, (SELECT COUNT(*) FROM image_link pub WHERE (pub.parent_id = a.id) AND (pub.type_id = {$this->gallery->siteSection->id->value}) AND (pub.access LIKE 'public')) public_pages
	, DATE_FORMAT(a.release_date,'%m/%d/%Y') release_date
	, a.`access`
	, a.`layout`
	, a.tn_id
    , IFNULL(mini.path, med.path) tn_path
    , IFNULL(mini.width, med.width) tn_width
    , IFNULL(mini.height, med.height) tn_height
	, mini.path mini_path
	, mini.width mini_width
	, mini.height mini_height
	, med.path med_path
	, med.width med_width
	, med.height med_height
	, full.path full_path
	, full.width full_width
	, full.height full_height
FROM `album` a 
LEFT JOIN 
(
	image_link il 
	INNER JOIN images full ON il.fullres_id = full.id
	LEFT JOIN images med ON il.med_id = med.id
	LEFT JOIN images mini ON il.mini_id = mini.id
) ON (a.tn_id = il.id) 
{$this->sqlClause} 
SQL;
		return ($query);
	}

	/**
	 * Returns SQL to retrieve album listings.
	 * @return string SQL string used to retrieve album listings
	 */
	protected function formatListingsQuery( )
	{
		$lower_limit = $upper_limit = "";
		$this->formatQueryLimits($lower_limit, $upper_limit);

		$query = $this->formatListingsSelectQuery();
		$query .= <<<SQL
ORDER BY a.slot, a.id DESC 
{$lower_limit}{$upper_limit}
SQL;
		return ($query);
	}

	/**
	 * Retrieves recordset containing album titles matching the current filters.
	 * @return \mysqli_result Album titles data set.
	 * @throws InvalidQueryException
	 * @throws ResourceNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Exception
	 */
	public function searchTitles()
	{
		$this->connectToDatabase();
		$sQuery = "CALL albumTitlesSelect(".
			$this->page->escapeSQL($this->mysqli).",".
			$this->listingsLength->escapeSQL($this->mysqli).",".
			$this->keyword->escapeSQL($this->mysqli).",".
			$this->siteSection->id->escapeSQL($this->mysqli).",".
			"@total_matches);SELECT CAST(@total_matches AS UNSIGNED) as `total_matches`";
		if (!$this->mysqli->multi_query($sQuery)) {
			throw new InvalidQueryException("Error retrieving titles: {$this->mysqli->error}");
		}
		$data = $this->mysqli->store_result();
		if (!$data) {
			throw new ResourceNotFoundException("Error retrieving title: {$this->mysqli->error}");
		}
		/* get record count from sproc results */
		$this->getSprocPageCount();
		return ($data);
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
	public function getContentProperties ($content_type_id=null)
	{
		if ($content_type_id>0) {
			$this->siteSection->id->value = $content_type_id;
			$this->contentProperties->section_id->value = $content_type_id;
		}
		$this->siteSection->read();
		$this->contentProperties->retrieveSectionProperties();
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
		if (!isset($this->listingsLength->value)) {
			$this->listingsLength->value = $this->DEFAULT_PAGE_LEN();
		}
		if ($this->next->value=="") {
			$this->next->value = "view";
		}
		$this->gallery->collectFilterValues($save_filters);
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
		$this->queryString .= preg_replace("/^\?/", "&", $gqs);
		return ($this->queryString);
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
	 * Retrieves from database the uri of the page used to display details for this content type.
	 * @return string Uri of details page.
	 * @throws InvalidQueryException
	 */
	public function getDetailsURI()
	{
		if ($this->siteSection->id->value===null || $this->siteSection->id->value<1) {
			return ("");
		}

		$query = "SELECT details_uri FROM section_operations WHERE section_id = {$this->siteSection->id->value}";
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			return($data[0]->details_uri);
		}
		return ("");
	}
}
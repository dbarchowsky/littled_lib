<?php
namespace Littled\Filters;

use Littled\Database\AppContentBase;
use Littled\Exception\NotImplementedException;
use Littled\PageContent\PageUtils;
use Littled\Validation\Validation;


/**
 * Class filter_collection_class
 * Filter collection base class, inherits from db_connection_class
 * @package Littled\Filters
 */
class FilterCollection extends AppContentBase
{
	/** @var BooleanContentFilter Flag to suppress the display of the listings. */
	public $displayListings;
	/** @var StringContentFilter Token indicating the next operation to take, typically after editing a record. */
	public $next;
	/** @var integer Record id of the next record in the sequence matching the current filter values. */
	public $nextRecordId;
	/** @var IntegerContentFilter Current page number. */
	public $page;
	/** @var integer Total number of pages available for records matching the current filter values. */
	public $pageCount;
	/** @var IntegerContentFilter Maximum number of records to display per page. */
	public $listingsLength;
	/** @var integer Record id of the previous record in the sequence matching the current filter values. */
	public $previousRecordId;
	/** @var string SQL query string used to fetch the current record set. */
	public $queryString;
	/** @var integer Total number of records matching the current filter values. */
	public $recordCount;
	/** @var string URL to redirect back to, if specified */
	public $referringURL;
	/** @var string SQL WHERE clause matching the current filter values. */
	public $sqlClause;
	/** @var integer Default page length override. */
	public $defaultPageLength;

	const PAGE_PARAM = 'p';
	const LISTINGS_LENGTH_PARAM = 'pl';
	const NEXT_OPERATION_PARAM= 'next';
	const FILTER_PARAM = 'filt';
	const REFERRING_URL_PARAM = 'ref';
	const LINKS_OFFSET = 5;
	const LINKS_END_LENGTH = 2;

	const COOKIE_NAME = null;
	public static function COOKIE_NAME () { return(self::COOKIE_NAME); }
	public static function TABLE_NAME() { return(""); }
	public static function DEFAULT_LISTINGS_LENGTH() { return (20); }

	/**
	 * class constructor
	 * @param string $param_prefix A value to prepended to all of the core parameters of the base class.
	 */
	function __construct ($param_prefix='')
	{
		parent::__construct();
		$this->page = new IntegerContentFilter("Page", $param_prefix.$this::PAGE_PARAM, null, null, $this::COOKIE_NAME());
		$this->listingsLength = new IntegerContentFilter("Page length", $param_prefix.$this::LISTINGS_LENGTH_PARAM, null, null, $this::COOKIE_NAME());
		$this->next = new StringContentFilter("Next", $param_prefix.$this::NEXT_OPERATION_PARAM, '', 16, $this::COOKIE_NAME());
		$this->displayListings = new BooleanContentFilter("Display listings", $param_prefix.$this::FILTER_PARAM, false, null, $this::COOKIE_NAME());
		$this->referringURL = '';
	}

	/**
	 * Calculate the total number of pages for the listings based on the total number of records and the length of the pages.
	 * @param int[optional] $rec_count Total number of records.
	 * @param int[optional] $page_len Number of records displayed on the individual pages.
	 * @return int Total number of pages in the listings.
	 */
	public function calcPageCount($rec_count=null, $page_len=null )
	{
		if ($rec_count===null) {
			$rec_count = $this->recordCount;
		}
		if ($page_len===null) {
			$page_len = $this->listingsLength->value;
		}
		if ($page_len===null) {
			$page_len = $this->recordCount;
		}
		return (PageUtils::calculateRowCount($rec_count, $page_len));
	}

	/**
	 * returns the current page value translated into the number of records from the beginning of the record set
	 * @return integer number of records from the beginning of the record set of the first record on this page
	 */
	public function calcRecordPosition()
	{
		return (($this->page->value-1)*$this->listingsLength->value);
	}

	/**
	 * Specialized routine for collectin the "display listings" setting.
	 * - Don't check cookies for this filter's value.
	 * - If the input value is set to "filter", set the object's property value
	 * to TRUE.
	 */
	protected function collectDisplayListingsSetting()
	{
		/* don't get "display listings" value from cookies */
		$this->displayListings->collectValue(false);
		if ($this->displayListings->value===null) {
			$str_value = Validation::collectRequestVar($this->displayListings->key);
			if (strtolower($str_value)=="filter") {
				$this->displayListings->value = true;
			}
		}
	}

	/**
	 * Extract values for listings filters from form data and query string.
	 * @param boolean $save_filters (optional) If set to TRUE, save all filter values in session variables.
	 * @return void
	 */
	public function collectFilterValues($save_filters=true)
	{
		$excluded_properties = array("displayListings");

		$this->referringURL = Validation::collectRequestVar($this::REFERRING_URL_PARAM);

		foreach ($this as $key => &$filter) {
			if (($filter instanceof ContentFilter) && (!in_array($key, $excluded_properties))) {
				$filter->collectValue();
			}
		}

		$this->collectDisplayListingsSetting();

		/* save filters values in session */
		if ($save_filters) {
			foreach ($this as $key => &$filter) {
				if (($filter instanceof ContentFilter) && (!in_array($key, $excluded_properties))) {
					$_SESSION[$this::COOKIE_NAME][$filter->key] = $filter->value;
				}
			}
		}

		/* default values if property values were not supplied */
		if (!isset($this->page->value)) {
			$this->page->value = 1;
		}
		if (!isset($this->listingsLength->value)) {
			$this->listingsLength->value = $this::DEFAULT_LISTINGS_LENGTH();
		}

		if ($this->next->value=="") {
			$this->next->value = "view";
		}
	}

	/**
	 * Format and return the query string that will retrieve filters listings data.
	 * @throws NotImplementedException
	 */
	protected function formatListingsQuery()
	{
		throw new NotImplementedException(get_class($this)."::formatListingsQuery() not implemented.");
	}

	/**
	 * Derived classes will return a string containing a WHERE clause used to filter listings.
	 */
	protected function formatQueryClause()
	{
		/* placeholder for derived classes */
	}

	/**
	 * Formats SQL clauses to the offset and page size of the recordset.
	 * @return array First element is the lower limit value and the 2nd element is the upper limit value.
	 */
	public function getQueryLimits()
	{
		if ($this->page->value > 0) {
			$lower_limit =($this->page->value-1)*$this->listingsLength->value;
		}
		else {
			$lower_limit = 1;
		}
		if ($this->listingsLength->value > 0) {
			$upper_limit = $this->listingsLength->value;
		}
		elseif ($this->recordCount > 0) {
			$upper_limit = $this->recordCount;
		}
		else {
			$this->getPageCount();
			$upper_limit = $this->recordCount;
		}
		return(array($lower_limit, $upper_limit));
	}

	/**
	 * Returns a query string containing name/value pairs for each filter that currently holds a value.
	 * @param array|null[optional] $exclude Array containing the names of parameters that should not be included in the query string.
	 * @return string Query string containing filters
	 */
	public function formatQueryString ($exclude=null )
	{
		$excluded_properties = array('recordCount', 'pageCount');
		$qs_array = array();
		$this->queryString = "";
		foreach($this as $key => $filter) {
			if (($filter instanceof ContentFilter) && (!in_array($key, $excluded_properties))) {
				if ($exclude===null || !in_array($filter->key, $exclude)) {
					$qs_array[] = $filter->formatQueryString();
				}
			}
		}
		if (count($qs_array) > 0) {
			$this->queryString = '?'.implode('&', array_filter($qs_array, function($value) {
				return ($value != '');
			}));
		}
		return ($this->queryString);
	}

	/**
	 * Retrieves total number of records matching the current filter values.
	 */
	protected function getPageCount()
	{
		$this->formatQueryClause();

		$query = "SELECT COUNT(DISTINCT a.`id`) AS `count` ".
			"FROM `?` a ".
			"LEFT JOIN `image_link` p ON a.`id` = p.`parent_id` ".
			$this->sqlClause;
		$data = $this->fetchRecords($query, array($this::TABLE_NAME()));

		$this->recordCount = $data[0]->count;
		$this->calcPageCount();
	}

	/**
	 * Store total number of matching results for later use when rendering listings.
	 * @throws \Exception
	 */
	protected function getSprocPageCount()
	{
		if ($this->mysqli->more_results()) {
			while ($this->mysqli->more_results()) {
				/** @var \mysqli_result $result */
				$this->mysqli->next_result();
				if ($result = $this->mysqli->store_result()) {
					$this->recordCount = $result->fetch_object()->total_matches;
					$this->calcPageCount();
					break;
				}
			}
		}
		else {
			throw new \Exception("No record count results are available. ");
		}
	}

	/**
	 * Returns a string containing all filters in the collection expressed as a JavaScript array.
	 * @param array $exclude Array containing the names of any filters to not add to the string.
	 * @return string JSON-encoded string
	 */
	public function jsonEncode($exclude=null)
	{
		$o = array();
		foreach ($this as $key => $filter) {
			if ($filter instanceof ContentFilter) {
				if (strlen(''.$filter->value)>0) {
					if ($exclude===null || !in_array($key, $exclude)) {
						$o[$filter->key] = $filter->value;
					}
				}
			}
		}
		return(json_encode($o));
	}

	/**
	 * Intended to be implemented in inherited classes that have either a $contentOperations or $contentProperties property.
	 * @return string
	 */
	public function pluralLabel( )
	{
		return('');
	}

	/**
	 * Print out current filters as hidden inputs to be included in a form in order to preserve the filters after form submission.
	 * @param array $exclude List of parameter names that should not be included in the hidden form inputs.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 */
	public function preserveInForm ($exclude=null)
	{
		if ($exclude==null) {
			$exclude = array();
		}
		$exclude = $exclude + array('recordCount', 'pageCount');
		foreach($this as $key => $filter) {
			/** @var ContentFilter $filter */
			if ($filter instanceof ContentFilter) {
				if (!in_array($filter->key, $exclude)) {
					$filter->saveInForm();
				}
			}
		}
	}

	/**
	 * Retrieves listings data from database using object's filter values.
	 * @return array Listings data
	 */
	public function retrieveListings()
	{
		$data = array();
		try {
			$data = $this->fetchRecords($this->formatListingsQuery());
		}
		catch(\Exception $ex) {
			print("<div class=\"alert alert-error\">Error retrieving listings: ".$ex->getMessage());
		}
		return ($data);
	}
}

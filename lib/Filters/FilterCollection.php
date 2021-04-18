<?php
namespace Littled\Filters;

use Littled\Database\AppContentBase;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageUtils;
use Littled\Validation\Validation;
use Exception;
use mysqli_result;


/**
 * Class filter_collection_class
 * Filter collection base class, inherits from db_connection_class
 * @package Littled\Filters
 */
class FilterCollection extends AppContentBase
{
    const PAGE_PARAM = 'p';
    const LISTINGS_LENGTH_PARAM = 'pl';
    const NEXT_OPERATION_PARAM= 'next';
    const FILTER_PARAM = 'filt';
    const REFERRING_URL_PARAM = 'ref';
    const LINKS_OFFSET = 5;
    const LINKS_END_LENGTH = 2;

	/** @var BooleanContentFilter Flag to suppress the display of the listings. */
	public $display_listings;
	/** @var StringContentFilter Token indicating the next operation to take, typically after editing a record. */
	public $next;
	/** @var integer Record id of the next record in the sequence matching the current filter values. */
	public $next_record_id;
	/** @var IntegerContentFilter Current page number. */
	public $page;
	/** @var integer Total number of pages available for records matching the current filter values. */
	public $page_count;
	/** @var IntegerContentFilter Maximum number of records to display per page. */
	public $listings_length;
	/** @var integer Record id of the previous record in the sequence matching the current filter values. */
	public $previous_record_id;
	/** @var string SQL query string used to fetch the current record set. */
	public $query_string;
	/** @var integer Total number of records matching the current filter values. */
	public $record_count;
	/** @var string URL to redirect back to, if specified */
	public $referer_uri;
	/** @var string SQL WHERE clause matching the current filter values. */
	public $sql_clause;
	/** @var string Key for cookie used to preserve filter settings. */
	protected static $cookie_key;
	/** @var int Default number of line items to display in listings */
	protected static $default_listings_length;
	/** @var string String to add to parameter names to make them specific to the current type of listings. */
	protected static $key_prefix;
	/** @var string Name of table storing listings content. */
	protected static $table_name;

	/**
	 * class constructor
     * @throws NotImplementedException
	 */
	function __construct ()
	{
		parent::__construct();
		$this->page = new IntegerContentFilter("Page", $this->getLocalKey($this::PAGE_PARAM), null, null, $this::getCookieKey());
		$this->listings_length = new IntegerContentFilter("Page length", $this->getLocalKey($this::LISTINGS_LENGTH_PARAM), null, null, $this::getCookieKey());
		$this->next = new StringContentFilter("Next", $this->getLocalKey($this::NEXT_OPERATION_PARAM), '', 16, $this::getCookieKey());
		$this->display_listings = new BooleanContentFilter("Display listings", $this->getLocalKey($this::FILTER_PARAM), false, null, $this::getCookieKey());
		$this->referer_uri = '';
	}

	/**
	 * Calculate the total number of pages for the listings based on the total number of records and the length of the pages.
	 * @param int[optional] $rec_count Total number of records.
	 * @param int[optional] $page_len Number of records displayed on the individual pages.
	 * @return int Total number of pages in the listings.
	 */
	public function calcPageCount($rec_count=null, $page_len=null): int
	{
		if ($rec_count===null) {
			$rec_count = $this->record_count;
		}
		if ($page_len===null) {
			$page_len = $this->listings_length->value;
		}
		if ($page_len===null) {
			$page_len = $this->record_count;
		}
		return (PageUtils::calculateRowCount($rec_count, $page_len));
	}

	/**
	 * returns the current page value translated into the number of records from the beginning of the record set
	 * @return int number of records from the beginning of the record set of the first record on this page
	 */
	public function calcRecordPosition(): int
	{
		return (($this->page->value-1)*$this->listings_length->value);
	}

	/**
	 * Specialized routine for collectin the "display listings" setting.
	 * - Don't check cookies for this filter's value.
	 * - If the input value is set to "filter", set the object's property value
	 * to TRUE.
	 */
	protected function collectDisplayListingsSetting(): void
	{
		/* don't get "display listings" value from cookies */
		$this->display_listings->collectValue(false);
		if ($this->display_listings->value===null) {
			$str_value = Validation::collectRequestVar($this->display_listings->key);
			if (strtolower($str_value)=="filter") {
				$this->display_listings->value = true;
			}
		}
	}

	/**
	 * Extract values for listings filters from form data and query string.
	 * @param bool $save_filters (optional) If set to TRUE, save all filter values in session variables.
     * @throws NotImplementedException
	 */
	public function collectFilterValues($save_filters=true): void
	{
		$excluded_properties = array("displayListings");

		$this->referer_uri = Validation::collectRequestVar($this::REFERRING_URL_PARAM);

		foreach ($this as $key => $filter) {
			if (($filter instanceof ContentFilter) && (!in_array($key, $excluded_properties))) {
				$filter->collectValue();
			}
		}

		$this->collectDisplayListingsSetting();

		/* save filters values in session */
		if ($save_filters) {
			foreach ($this as $key => $filter) {
				if (($filter instanceof ContentFilter) && (!in_array($key, $excluded_properties))) {
					$_SESSION[$this->getCookieKey()][$filter->key] = $filter->value;
				}
			}
		}

		/* default values if property values were not supplied */
		if (!isset($this->page->value)) {
			$this->page->value = 1;
		}
		if (!isset($this->listings_length->value)) {
			$this->listings_length->value = $this->getDefaultListingsLength();
		}

		if ($this->next->value=="") {
			$this->next->value = "view";
		}
	}

	/**
	 * Format and return the query string that will retrieve filters listings data.
	 * @throws NotImplementedException
	 */
	protected function formatListingsQuery(): string
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
     * Returns a query string containing name/value pairs for each filter that currently holds a value.
     * @param ?array $exclude (Optional) Array containing the names of parameters that should not be included in the query string.
     * @return string Query string containing filters
     */
    public function formatQueryString (?array $exclude=null ): string
    {
        $excluded_properties = array('recordCount', 'pageCount');
        $qs_array = array();
        $this->query_string = "";
        foreach($this as $key => $filter) {
            if (($filter instanceof ContentFilter) && (!in_array($key, $excluded_properties))) {
                if ($exclude===null || !in_array($filter->key, $exclude)) {
                    $qs_array[] = $filter->formatQueryString();
                }
            }
        }
        if (count($qs_array) > 0) {
            $this->query_string = '?'.implode('&', array_filter($qs_array, function($value) {
                    return ($value != '');
                }));
        }
        return ($this->query_string);
    }

    /**
     * Abstract method for cookie key getter. Child classes will set the default value of the cookie key in their
     * implementations of the method.
     * @return string
     * @throws NotImplementedException
     */
	public function getCookieKey(): string
    {
        throw new NotImplementedException(get_class($this)."::getCookieKey() not implemented.");
    }

    /**
     * Abstract method for default listings length getter. Child classes will set an initial value for the property in
     * their implementations of the method.
     * @throws NotImplementedException
     */
    public function getDefaultListingsLength(): string
    {
        throw new NotImplementedException(get_class($this)."::getDefaultListingsLength() not implemented.");
    }

    /**
     * Abstract method for default query string variable name prefix. Child classes will set a default value for the
     * property in their implementation of the method.
     * @return string
     * @throws NotImplementedException
     */
    public function getKeyPrefix(): string
    {
        throw new NotImplementedException(get_class($this)."::getKeyPrefix() not implemented.");
    }

    /**
     * Returns a localized name for a query string variable that will hold the value of one of the filters.
     * @param string $base_key Base name of the variable to be added to a lcoalized prefix.
     * @return string
     * @throws NotImplementedException
     */
    public function getLocalKey(string $base_key): string
    {
        return ($this->getKeyPrefix().$base_key);
    }

    /**
	 * Formats SQL clauses to the offset and page size of the recordset.
	 * @return array First element is the lower limit value and the 2nd element is the upper limit value.
	 * @throws InvalidQueryException
     * @throws NotImplementedException
	 */
	public function getQueryLimits(): array
	{
		if ($this->page->value > 0) {
			$lower_limit =($this->page->value-1)*$this->listings_length->value;
		}
		else {
			$lower_limit = 1;
		}
		if ($this->listings_length->value > 0) {
			$upper_limit = $this->listings_length->value;
		}
		elseif ($this->record_count > 0) {
			$upper_limit = $this->record_count;
		}
		else {
			$this->getPageCount();
			$upper_limit = $this->record_count;
		}
		return(array($lower_limit, $upper_limit));
	}

	/**
	 * Retrieves total number of records matching the current filter values.
	 * @throws InvalidQueryException
     * @throws NotImplementedException
	 */
	protected function getPageCount()
	{
		$this->formatQueryClause();

		$query = "SEL"."ECT COUNT(DISTINCT a.`id`) AS `count` ".
			"FROM `".$this->getTableName()."` a ".
			"LEFT JOIN `image_link` p ON a.`id` = p.`parent_id` ".
			$this->sql_clause;
		$data = $this->fetchRecords($query);

		$this->record_count = $data[0]->count;
		$this->calcPageCount();
	}

	/**
	 * Store total number of matching results for later use when rendering listings.
	 * @throws Exception
	 */
	protected function getSprocPageCount(): void
	{
		if ($this->mysqli->more_results()) {
			while ($this->mysqli->more_results()) {
				/** @var mysqli_result $result */
				$this->mysqli->next_result();
				if ($result = $this->mysqli->store_result()) {
					$this->record_count = $result->fetch_object()->total_matches;
					$this->calcPageCount();
					break;
				}
			}
		}
		else {
			throw new Exception("No record count results are available. ");
		}
	}

    /**
     * Abstract method for table name getter. Child classes will set initial value within the method.
     * @return string
     * @throws NotImplementedException
     */
	public function getTableName(): string
    {
        throw new NotImplementedException("getTableName() not implemented in ".get_class($this));
    }

	/**
	 * Returns a string containing all filters in the collection expressed as a JavaScript array.
	 * @param ?array $exclude (Optional) Array containing the names of any filters to not add to the string.
	 * @return string JSON-encoded string
	 */
	public function jsonEncode(?array $exclude=null): string
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
	public function pluralLabel( ): string
	{
		return('');
	}

	/**
	 * Print out current filters as hidden inputs to be included in a form in order to preserve the filters after form submission.
	 * @param array $exclude List of parameter names that should not be included in the hidden form inputs.
     * @throws ResourceNotFoundException
	 */
	public function preserveInForm ($exclude=null): void
	{
		if ($exclude==null) {
			$exclude = array();
		}
		$exclude = $exclude + array('recordCount', 'pageCount');
		foreach($this as $filter) {
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
	public function retrieveListings(): array
	{
		$data = array();
		try {
			$data = $this->fetchRecords($this->formatListingsQuery());
		}
		catch(Exception $ex) {
			print("<div class=\"alert alert-error\">Error retrieving listings: ".$ex->getMessage());
		}
		return ($data);
	}

    /**
     * Setter for key used to preserve filter values in cookie data.
     * @param string $key
     */
	public function setCookieKey(string $key): void
    {
        static::$cookie_key = $key;
    }

    /**
     * Setter for default listings length property value.
     * @param int $length
     */
    public function setDefaultListingsLength(int $length): void
    {
        static::$default_listings_length = $length;
    }

    /**
     * Key prefix setter.
     * @param $prefix
     */
    public function setKeyPrefix($prefix): void
    {
        static::$key_prefix = $prefix;
    }

    /**
     * Setter for name of table containing listing content.
     * @param string $table
     */
	public function setTableName(string $table): void
    {
        static::$table_name = $table;
    }
}

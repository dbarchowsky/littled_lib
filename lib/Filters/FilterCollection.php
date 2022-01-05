<?php
namespace Littled\Filters;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\PageUtils;
use Littled\Validation\Validation;
use Exception;
use mysqli_result;


/**
 * Class filter_collection_class
 * Filter collection base class, inherits from db_connection_class
 * @package Littled\Filters
 */
class FilterCollection extends FilterCollectionProperties
{
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
	 * Specialized routine for collection the "display listings" setting.
	 * - Don't check cookies for this filter's value.
	 * - If the input value is set to "filter", set the object's property value
	 * to TRUE.
	 */
	protected function collectDisplayListingsSetting()
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
	public function collectFilterValues(bool $save_filters=true)
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
	protected function getSprocPageCount()
	{
		if ($this->mysqli->more_results()) {
			while ($this->mysqli->more_results()) {
				/** @var mysqli_result $result */
				$this->mysqli->next_result();
				if ($result = $this->mysqli->store_result()) {
					$this->record_count = $result->fetch_object()->total_matches;
					$this->page_count = $this->calcPageCount();
					break;
				}
			}
		}
		else {
			throw new Exception("No record count results are available. ");
		}
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
	 * @param ?array $exclude List of parameter names that should not be included in the hidden form inputs.
     * @throws ResourceNotFoundException
	 */
	public function preserveInForm (?array $exclude=null)
	{
		$exclude = ($exclude ?? array()) + array('recordCount', 'pageCount');
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
	 * @return mysqli_result Listings data
     * @throws Exception
	 */
	public function retrieveListings(): mysqli_result
	{
        $result = null;
        $this->connectToDatabase();
		try {
			$result = $this->mysqli->query($this->formatListingsQuery());
		}
		catch(Exception $ex) {
            ContentUtils::printError($ex->getMessage());
		}
        if (!$result) {
            throw new Exception("Error retrieving listings: ".$this->mysqli->error);
        }
        return $result;
	}
}

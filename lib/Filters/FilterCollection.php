<?php
namespace Littled\Filters;

use Littled\App\LittledGlobals;
use Littled\Database\DBUtils;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageUtils;
use Littled\Validation\Validation;
use Exception;


/**
 * Class filter_collection_class
 * Filter collection base class, inherits from db_connection_class
 * @package Littled\Filters
 */
class FilterCollection extends FilterCollectionProperties
{
    /** @var string */
    protected const RECORD_COUNT_ARG = '@total_matches';

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
        if ($this->page->value===null) {
            $this->page->value = 1;
        }
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
	 * @param bool $save_filters (Optional) If set to TRUE, save all filter values in session variables.
     * @param array $excluded_properties (Optional) A list of keys to exclude from collection.
     * @throws NotImplementedException
	 */
	public function collectFilterValues(bool $save_filters=true, array $excluded_properties=[])
	{
        $ref = Validation::collectStringRequestVar(LittledGlobals::REFERER_KEY);
		$this->referer_uri = (($ref===null)?(''):($ref));

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
			$this->listings_length->value = $this::getDefaultListingsLength();
		}

		if ($this->next->value=="") {
			$this->next->value = "view";
		}
	}

	/**
	 * Format and return the query string that will retrieve filters listings data.
     * @returns array Returns an array where the first element is a sql query followed by an array of variables to bind
     * to the query. See mysqli_statement::bind_param() for specs of the array variables. The 2nd element of the
     * array is a string describing the types of the following values in the array, e.g. 'iissiis' for int, int,
     * string, int, etc.
	 */
	protected function formatListingsQuery(): array
	{
		return array('', '', null);
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
	 * Returns the procedure call, type string, and arguments used in the searchTitles() method.
	 * @return array
	 */
	protected function formatTitleSearchQuery(): array
	{
		return array('', '', null);
	}

    /**
     * Returns the index within the set of all records matching the listings filters of the first record to be
     * displayed on the current page of listings.
     * @return int
     */
    public function getListingsStartOffset(): int
    {
        return (($this->page->value - 1) * $this->listings_length->value) + 1;
    }

    /**
     * Returns the index within the set of all records matching the listings filters of the last record to be
     * displayed on the current page of listings.
     * @param int $starting_offset
     * @return int
     */
    public function getListingsEndOffset(int $starting_offset): int
    {
        if (($starting_offset + $this->listings_length->value - 1) <= $this->record_count) {
            return $starting_offset + $this->listings_length->value - 1;
        }
        else {
            return $this->record_count;
        }
    }

    /**
	 * Formats SQL clauses to the offset and page size of the recordset.
	 * @return array First element is the lower limit value and the 2nd element is the upper limit value.
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
     * @throws NotImplementedException
     * @throws Exception
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
     * Returns the mid-point page number of listings page link sequence.
     * @param int $half_length
     * @return int
     */
    public function getPageListMidPoint(int $half_length): int
    {
        if ($this->page->value < $half_length) {
            return $half_length;
        }
        else if ($this->page->value > ($this->page_count-$half_length+1)) {
            return $this->page_count - $half_length + 1;
        }
        return $this->page->value;
    }

	/**
	 * Store total number of matching results for later use when rendering listings.
	 * @throws Exception
	 */
	protected function getProcedurePageCount()
	{
        $this->record_count = $this->page_count = 0;
        $result = $this->mysqli->query('SELECT CAST('.self::RECORD_COUNT_ARG.' AS UNSIGNED) AS `total_matches`');
        if (!$result) {
            throw new Exception('Error getting record count: '.$this->mysqli->error);
        }
        $r = $result->fetch_object();
        $result->free();

        $this->record_count = $r->total_matches;
        $this->page_count = $this->calcPageCount();
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
	 * @return array Listings data
     * @throws Exception
	 */
	public function retrieveListings(): array
	{
        $args = $this->formatListingsQuery();
        $data = call_user_func_array([$this, 'fetchRecords'], $args);

        // If the query is a procedure that calculates record count, retrieve that total record count
        if(DBUtils::isProcedure($args[0])) {
            $pattern = '/'.self::RECORD_COUNT_ARG.'/';
            if (preg_match($pattern, $args[0])) {
                $this->getProcedurePageCount();
            }
        }
        else {
            $this->getPageCount();
        }
        return $data;
	}

	/**
	 * Retrieves the record ids of the records adjacent to the requested record in the record listings sequence.
	 * The order of the records preserves the current filters being applied to the listings.
	 * @param int $record_id The id of the current record from the record listings sequence.
	 * @throws Exception
	 */
	public function retrieveNeighborIds(int $record_id)
	{
		// reset previous and next record id property values
		$this->previous_record_id = null;
		$this->next_record_id = null;

		// retrieve the current page of listings in order to look up the current record's position
		$data = $this->retrieveListings();
		if (count($data) < 1) {
			// no matching records found
			return;
		}

		$index = null;
		foreach ($data as $row) {
			if ($row->id === $record_id) {
				$index = (int)$row->index;
				break;
			}
		}
		if ($index===null) {
			return;
		}

		if ($index===0) {
			/**
			 * Current location is the first record in the page of listings.
			 * Load the previous page of listings to get the previous record id in the sequence.
			 */
			$this->next_record_id = ((count($data)>1) ? ($data[$index+1]->id) : (null));
			if ($this->page->value > 1) {
				$this->page->value--;
				$data = $this->retrieveListings();
				if (count($data) > 0) {
					$this->previous_record_id = end($data)->id;
				}
				$this->page->value++;
			}
		}
		elseif ($index===count($data)-1) {
			/**
			 * Current location is the last record in the page of listings.
			 * Load the next page of listings to get the next record id in the sequence.
			 */
			$this->previous_record_id = $data[$index-1]->id;
			if ($this->page->value<$this->page_count) {
				$this->page->value++;
				$data = $this->retrieveListings();
				if (count($data) > 0) {
					$this->next_record_id = $data[0]->id;
				}
				$this->page->value--;
			}
		}
		else {
			// current location has neighbors on both sides within this record set
			$this->previous_record_id = $data[$index-1]->id;
			$this->next_record_id = $data[$index+1]->id;
		}
	}

	/**
	 * Retrieves recordset containing codes or titles matching the current keyword filter that can then be used to populate autocomplete routines.
	 * @return array Record set containing matching package records
	 * @throws Exception
	 */
	public function searchTitles(): array
	{
		$args = $this->formatTitleSearchQuery();
		if (count($args) > 1 && $args[1]) {
			$data = call_user_func_array([$this, 'fetchRecords'], $args);
		}
		else {
			$data = $this->fetchRecords($args[0]);
		}
		$this->getProcedurePageCount();
		return $data;
	}
}

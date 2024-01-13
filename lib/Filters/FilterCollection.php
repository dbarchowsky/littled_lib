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
 * Collection of core filters used for navigation, filtering listings records, and preserving the filtering state.
 */
class FilterCollection extends FilterCollectionProperties
{
    /** @var string */
    protected const RECORD_COUNT_ARG = '@total_matches';
	protected static bool $autoload_default=false;
	/** @var int Value calculated before retrieving listings by multiplying page value by listings length  */
	protected int $listings_offset;

	/**
	 * Calculate the total number of pages for the listings based on the total number of records and the length of the pages.
	 * @param ?int $rec_count Optional total number of records.
	 * @param ?int $page_len Optional number of records displayed on the individual pages.
	 * @return int Total number of pages in the listings.
	 */
	public function calcPageCount(?int $rec_count=null, ?int $page_len=null): int
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
	 * Returns the number of records on all the pages preceding the current page in the listings.
	 * @return int Number of records on all the pages prior to the current page in the listings.
	 */
	public function calculateOffsetToPage(): int
	{
        if ($this->page->value===null) {
            $this->page->value = 1;
        }
		return (($this->page->value-1)*$this->listings_length->value);
	}

    /**
     * Returns the position of a given record within its page of listings data.
     * @param int $record_id Record id of the current record in the listings.
     * @param array $data Listings data from the current page in the listings.
     * @return ?int
     */
    protected function calculateRecordPositionOnPage(int $record_id, array $data): ?int
    {
        // calculate the index of the record preceding the current record
        $position = 0;
		$record_found = false;
        foreach ($data as $row) {
	        $position++;
            if ($row->id === $record_id) {
				$record_found=true;
                break;
            }
        }
        if (!$record_found) {
            return null;
        }
        return $position;
    }

	/**
	 * Specialized routine for collection the "display listings" setting.
	 * - Don't check cookies for this filter's value.
	 * - If the input value is set to "filter", set the object's property value to TRUE.
     * @param ?array $src Client request data that will override GET and POST data.
	 */
	protected function collectDisplayListingsSetting(?array $src=null)
	{
		/* don't get "display listings" value from cookies */
        $this->display_listings->value = null;
		$this->display_listings->collectValue(false, $src);
		if ($this->display_listings->value===null) {
			$str_value = Validation::collectRequestVar($this->display_listings->key, Validation::DEFAULT_REQUEST_FILTER, $src);
			if (strtolower(''.$str_value)=="filter") {
				$this->display_listings->value = true;
			}
		}
		if ($this->getAutoloadDefault()===true && $this->display_listings->value===null) {
			$this->display_listings->value = true;
		}
	}

	/**
	 * Extract values for listings filters from form data and query string.
	 * @param bool $save_filters Optional. If set to TRUE, save all filter values in session variables.
     * @param array $excluded_properties Optional list of keys to exclude from collection.
     * @param ?array $src Optional array containing data to use to extract request client data.
     * @throws NotImplementedException
	 */
	public function collectFilterValues(
        bool    $save_filters=true,
        array   $excluded_properties=[],
        ?array  $src=null )
	{
        $ref = Validation::collectStringRequestVar(
            LittledGlobals::REFERER_KEY,
            Validation::DEFAULT_REQUEST_FILTER,
            null,
            $src);
		$this->referer_uri = (($ref===null)?(''):($ref));

		foreach ($this as $key => $filter) {
			if (($filter instanceof ContentFilter) && (!in_array($key, $excluded_properties))) {
				$filter->collectValue(true, $src);
			}
		}

		$this->collectDisplayListingsSetting($src);

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
     * Returns the procedure call, type string, and arguments used in the searchTitles() method.
     * @return array
     */
    protected function formatKeywordSearchQuery(): array
    {
        return array('', '', null);
    }

    /**
	 * Format and return the query string that will retrieve filters listings data.
	 * @param bool $calculate_offset Optional flag to prevent the offset to the start of the records from being recalculated.
     * @returns array Returns an array where the first element is a sql query followed by an array of variables to bind
     * to the query. See mysqli_statement::bind_param() for specs of the array variables. The 2nd element of the
     * array is a string describing the types of the following values in the array, e.g. 'iissiis' for int, int,
     * string, int, etc.
	 */
	protected function formatListingsQuery(bool $calculate_offset=true): array
	{
		if ($calculate_offset) {
			$this->listings_offset = $this->getListingsStartOffset();
		}
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
     * @param string[]|null $exclude (Optional) Array containing the names of parameters that should not be included in the query string.
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
	 * Autoload listings setting getter.
	 * @return bool
	 */
	public function getAutoloadDefault(): bool
	{
		return static::$autoload_default;
	}

    /**
     * Returns the index within the set of all records matching the listings filters of the first record to be
     * displayed on the current page of listings.
     * @return int
     */
    public function getListingsStartOffset(): int
    {
		if ($this->page->value===null) {
			return 0;
		}
        return (($this->page->value - 1) * $this->listings_length->value);
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

	protected function listingsDataContainsNeighborIds(array $data, int $page_position): bool
	{
		if ($page_position > count($data)) {
			return false;
		}

		if ($page_position > 1 ) {
			if ($page_position < $this->listings_length->value) {
				// record is in the middle of the current page of listings. previous and next record ids are available.
				return true;
			}
			if ($this->page->value == $this->page_count && $page_position === count($data)) {
				// current record is the last record of the entire set of listings. store previous id & done.
				return true;
			}
		}
		if ($page_position===1 && $this->page->value===1) {
			if (count($data) > 1) {
				// current record is the first record in the entire set of records. store next record id & done.
				return true;
			}
			if (count($data) === 1 && $this->record_count===1) {
				// there is only one record in the entire set of listings. no previous or next record ids exist.
				return true;
			}
		}
		return false;
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
     * Assigns values to pagination properties.
     * @param PaginationValues $values Stored pagination values
     * @return void
     */
    protected function restorePaginationValues(PaginationValues $values)
    {
        $this->page->value = $values->page;
        $this->listings_length->value = $values->listings_length;
        $this->page_count = $values->page_count;
    }

    /**
     * Retrieves recordset containing codes or titles matching the current keyword filter that can then be used to populate autocomplete routines.
     * @return array Record set containing matching package records
     * @throws Exception
     */
    public function retrieveKeywordSearchResults(): array
    {
        $args = $this->formatKeywordSearchQuery();
        if (count($args) > 1 && $args[1]) {
            $data = call_user_func_array([$this, 'fetchRecords'], $args);
        }
        else {
            $data = $this->fetchRecords($args[0]);
        }
        $this->getProcedurePageCount();
        return $data;
    }

    /**
	 * Retrieves listings data from database using object's filter values.
	 * @param bool $calculate_offset Optional flag to prevent the offset to the first record in the listings from being recalculated prior to retrieving the listings records.
	 * @return array Listings data
     * @throws Exception
	 */
	public function retrieveListings(bool $calculate_offset=true): array
	{
        $args = $this->formatListingsQuery($calculate_offset);
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

        // retrieve current page of listings containing the record currently being viewed
        $data = $this->retrieveListings();
        if (count($data) < 1) {
            // no matching records found
            return;
        }

        // offset of the current record from the first record in the complete listings matching the filter values
        $page_position = $this->calculateRecordPositionOnPage($record_id, $data);
        if ($page_position===null) {
            return;
        }

		if ($this->listingsDataContainsNeighborIds($data, $page_position)) {
			$this->setNeighborIdsFromListingsData($data, $page_position);
			return;
		}

		// At this point, either the previous or next record exist outside the set of listings representing the current page of listings.
		$this->setOutOfBoundNeighborIds($data, $page_position);
	}

	/**
	 * Autoload listings setting setter.
	 * @param bool $autoload
	 * @return void
	 */
	public static function setAutoloadDefault(bool $autoload)
	{
		static::$autoload_default = $autoload;
	}

    /**
     * Extract neighboring record id values from listings data and assign those values to the object's neighbor id property values.
     * @param array $data Listings data
     * @param int $page_position Position of the current record within the set of listings.
     * @return void
     */
    protected function setNeighborIdsFromListingsData(array $data, int $page_position)
    {
		if ($page_position > 1) {
			$this->previous_record_id = $data[$page_position-2]->id;
		}
		if ($page_position < count($data)) {
			$this->next_record_id = $data[$page_position]->id;
		}
    }

	/**
	 * Runs a query to retrieve the record ids of the previous and next records in listings when they aren't available in the current set of listings.
	 * @param array $listings The current page of listings data.
	 * @param int $page_position The position of the active record within the active page of listings.
	 * @return void
	 * @throws Exception
	 */
	protected function setOutOfBoundNeighborIds(array $listings, int $page_position)
	{
        // Reset neighbor link values
        $this->previous_record_id = $this->next_record_id = null;

		// Save original settings.
		$original = new PaginationValues(
			$this->page->value,
			$this->listings_length->value,
			$this->page_count
		);

        $listings_position = $this->calculateOffsetToPage()+$page_position;

        if ($page_position === 1 && $this->page->value > 1) {
            // The preceding record is the last one on the previous page of listings.
            $this->page->value = $listings_position - 1;
            $this->listings_length->value = 1;
            $data = $this->retrieveListings();
            $this->previous_record_id = ((count($data) > 0) ? $data[0]->id : null);
            $this->restorePaginationValues($original);
        }
        elseif($page_position > 1 && count($listings) > $page_position-2) {
            // The preceding record exists in the listings data that was already retrieved.
            $this->previous_record_id = $listings[$page_position-2]->id;
        }

        if ($page_position === $this->listings_length->value && $this->page->value < $this->page_count) {
            // The following record is the first one on the next page of listings
            $this->page->value = $listings_position+1;
            $this->listings_length->value = 1;
            $data = $this->retrieveListings();
            $this->next_record_id = ((count($data) > 0) ? $data[0]->id : null);
            $this->restorePaginationValues($original);
        }
        elseif(count($listings) > $page_position) {
            // The following record exists within the listings data that was already retrieved.
            $this->next_record_id = $listings[$page_position]->id;
        }
	}
}

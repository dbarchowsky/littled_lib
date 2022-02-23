<?php
namespace Littled\Filters;

use Exception;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\SiteSection\ListingsKeywords;


class AlbumFilters extends ContentFilters
{
	protected static string $cookie_key = 'alb';
	/** @var int */
	protected static ?int $default_listings_length = 20;
	
	const RELEASED_AFTER_KEY = "fara";
	const RELEASED_BEFORE_KEY = "farb";

	/** @var StringContentFilter Keyword filter. */
	public StringContentFilter $keyword;
	/** @var StringContentFilter Album title filter. */
	public StringContentFilter $title;
	/** @var StringContentFilter Name filter. */
	public StringContentFilter $name;
	/** @var StringContentFilter Display date filter. */
	public StringContentFilter $date;
	/** @var StringContentFilter Access level filter. */
	public StringContentFilter $access;
	/** @var StringContentFilter Filters out records with release dates before the value of this property. */
	public $release_after;
	/** @var StringContentFilter Filters out records with release dates after the value of this property. */
	public $release_before;
	/** @var IntegerContentFilter Slot filter. */
	public IntegerContentFilter $slot;
	/** @var GalleryFilters Gallery filters. */
	public GalleryFilters $gallery;

	/**
	 * Class constructor
	 * @param int $content_type_id ID of the section of the site containing the listings. (From the site_section table.)
	 * @param int $page_content_type_id ID of the site_section representing the images within the listings (From the site_section table.)
	 * @param int[optional] $default_page_len Length of the pages of listings.
	 * @throws ConfigurationUndefinedException
	 */
	function __construct (int $content_type_id, int $page_content_type_id, int $default_page_len=10 )
	{
		parent::__construct();

		$this->keyword          = new StringContentFilter   ("keyword", Keyword::FILTER_KEY, '', 50, static::getCookieKey());
		$this->title            = new StringContentFilter   ("title", "fati", '', 50, static::getCookieKey());
		$this->date             = new StringContentFilter   ("date", "fadt", '', 20, static::getCookieKey());
		$this->access           = new StringContentFilter   ("access", "faac", '', 20, static::getCookieKey());
		$this->release_after    = new DateContentFilter     ("released after", $this::RELEASED_AFTER_KEY, '', 20, static::getCookieKey());
		$this->release_before   = new DateContentFilter     ("released before", $this::RELEASED_BEFORE_KEY, '', 20, static::getCookieKey());
		$this->slot             = new IntegerContentFilter  ("slot", "fasl", null, null, static::getCookieKey());

		$this->name = &$this->title;

		$this->gallery = new GalleryFilters();
		$this->previous_record_id = $this->next_record_id = 0;
	}

	/**
	 * Get filter values from query string and/or form data.
	 * @param bool[optional] $save_filters
	 * @return void
	 * @throws NotImplementedException
	 */
	public function collectFilterValues ($save_filters=true )
	{
		parent::collectFilterValues($save_filters);
		if (!isset($this->page->value)) {
			$this->page->value = 1;
		}
		if (!isset($this->listings_length->value)) {
			$this->listings_length->value = static::getDefaultListingsLength();
		}
		if ($this->next->value=="") {
			$this->next->value = "view";
		}
		$this->gallery->collectFilterValues($save_filters);
	}

	/**
	 * Returns SQL to retrieve album listings.
	 * @return array SQL string used to retrieve album listings
	 */
	protected function formatListingsQuery(): array
	{
		$album_id = null;
		return array("CALL albumFilteredListingsSelect(?,?,?,?,?,?,?,?,?,?,?,?,@total_matches)",
			'iiiisssssis',
			&$this->page->value,
			&$this->listings_length->value,
			&$album_id,
			&$this->content_properties->id->value,
			&$this->gallery->content_properties->id->value,
			&$this->title->value,
			&$this->date->value,
			&$this->release_after->value,
			&$this->release_before->value,
			&$this->access->value,
			&$this->slot->value,
			&$this->keyword->value);
	}

	/**
	 * @deprecated Use stored procedure instead.
	 * Returns select portion of SQL statement to retrieve album listings.
	 */
	protected function formatListingsSelectQuery( ): string
	{
		return ('');
	}

	/**
	 * @deprecated Use stored procedure instead.
	 * Create SQL string containing WHERE clause that will filter down the listings.
	 */
	public function formatQueryClause( ): string
	{
		return ('');
	}

	/**
	 * Overrides parent function to include image filters.
	 * @param ?array $exclude List of parameters to exclude from the query string.
	 * @return string Query string containing all filters as parameter/value pairs.
	 */
	public function formatQueryString ( ?array $exclude=null ): string
	{
		parent::formatQueryString($exclude);
		$gqs = $this->gallery->formatQueryString($exclude);
		$this->query_string .= preg_replace("/^\?/", "&", $gqs);
		return ($this->query_string);
	}

	/**
	 * Retrieve section properties.
	 * @param int|null $content_type_id The id of site section to retrieve properties for.
	 * @throws InvalidQueryException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidTypeException
	 */
	public function getAjaxProperties (?int $content_type_id=null)
	{
		if ($content_type_id > 0) {
			$this->content_properties->id->value = $content_type_id;
			$this->ajax_properties->section_id->value = $content_type_id;
		}
		if ($this->content_properties->id->value > 0) {
			$this->content_properties->read();
			$this->ajax_properties->retrieveContentProperties();
		}
	}

	/**
	 * Retrieves from database the uri of the page used to display details for this content type.
	 * @return string Uri of details page.
	 * @throws Exception
	 */
	public function getDetailsURI(): string
	{
		if ($this->content_properties->id->value===null || $this->content_properties->id->value<1) {
			return ("");
		}

		$query = "SELECT `details_uri` ".
			"FROM `section_operations` ".
			"WHERE `section_id` = ?";
		$content_type_id = $this->getContentTypeId();
		$data = $this->fetchRecords($query, 'i', $content_type_id);
		if (count($data) > 0) {
			return $data[0]->details_uri;
		}
		return '';
	}

	/**
	 * @deprecated Use stored procedure instead.
	 * Sets values of internal properties of the object to the number of records and pages in the current set of listings.
	 */
	public function getPageCount ()
	{
		/** deprecated */
	}

	/**
	 * Returns a string containing all filters in the collection expressed as a JavaScript array.
	 * Overrides parent to include image filters in the array.
	 * @param array|null $exclude Array containing the names of any filters to not add to the string.
	 * @return string String containing JavaScript array.
	 */
	public function jsonEncode(?array $exclude=null ): string
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
	 * @throws ResourceNotFoundException
	 */
	public function preserveInForm($exclude=null )
	{
		parent::preserveInForm($exclude);
		$this->gallery->preserveInForm($exclude);
	}

	/**
	 * Returns an appropriate label given the value of $count if $count requires the label to be pluralized.
	 * @param int|null $count Number determining if the label is plural or not.
	 * @return string Plural form of the record label if $count is not 1.
	 * @throws ConfigurationUndefinedException
	 */
	public function pluralLabel( ?int $count=null ): string
	{
		if ($count===null) {
			$count = $this->record_count;
		}
		if ($this->ajax_properties->label->value) {
			return($this->ajax_properties->pluralLabel($count));
		}
		if ($this->content_properties->label) {
			return($this->content_properties->pluralLabel($count));
		}
		return ('');
	}

	/**
	 * Returns context to use to render gallery listings.
	 * @param string $url Base URL to use for links back to this page.
	 * @param string $query_string Query string to use to preserve filter values when linking back to this page.
	 * @return array $context
	 * @throws Exception
	 */
	public function prepareListingsContext(string $url, string $query_string): array
	{
		$context = array(
			'filters' => $this,
			'url' => $url,
			'query_string' => htmlentities($query_string),
			'keywords' => new ListingsKeywords(null, $this->getContentTypeId()),
			'data' => array());
		if ($this->display_listings->value) {
			$data = $this->retrieveListings();
			foreach ($data as $row) {
				$row->name_widget_data = (object)array("id" => $row->id, "table" => $this->getTableName(), "value" => $row->title);
				$row->access_widget_data = (object)array("id" => $row->id, "table" => $this->getTableName(), "value" => $row->access);
				$row->date_widget_data = (object)array("id" => $row->id, "table" => $this->getTableName(), "value" => $row->release_date);
			}
			$context['data'] = $data;
		}
		return ($context);
	}

	/**
	 * Retrieves recordset containing album titles matching the current filters.
	 * @return array Album titles data set.
	 * @throws ResourceNotFoundException
	 * @throws Exception
	 */
	public function searchTitles(): array
	{
		$data = $this->fetchRecords(
			"CALL albumTitlesSelect(?,?,?,?,@total_matches)",
			'iisi',
			$this->page->value,
			$this->listings_length->value,
			$this->keyword->value,
			$this->content_properties->id->value);
		if (count($data) < 1) {
			throw new ResourceNotFoundException("Error retrieving album titles.");
		}
		/* get record count from procedure results */
		$this->getProcedurePageCount();
		return ($data);
	}
}
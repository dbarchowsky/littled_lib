<?php
namespace Littled\Filters;


use Littled\Exception\RecordNotFoundException;
use Littled\Keyword\Keyword;

/**
 * Class GalleryFilters
 * Handles retrieving listings of gallery content, made up of images with associated metadata.
 * @package Littled\Filters
 */
class GalleryFilters extends ContentFilters
{
	/** @var StringContentFilter Title filter. */
	public $title;
	/** @var IntegerContentFilter Album filter. */
	public $albumId;
	/** @var DateContentFilter Release date lower limit filter. */
	public $releaseAfter;
	/** @var DateContentFilter Release date upper limit filter. */
	public $releaseBefore;
	/** @var StringContentFilter Access filter. */
	public $access;
	/** @var StringContentFilter Keyword filter. */
	public $keyword;
	/** @var IntegerContentFilter Slot filter. */
	public $slot;
	/** @var StringContentFilter Name filter. */
	public $name;
	/** @var string URI of details page. */
	public $detailsURI;

	const COOKIE_NAME = "alp";
	const ALBUM_PARAM = "pid";
	const TYPE_PARAM = "tid";
	const ACCESS_PARAM = "filac";
	const START_DATE_PARAM = "filsd";
	const END_DATE_PARAM = "filed";
	const SLOT_PARAM = "filsl";
	const DEFAULT_PAGE_LEN = 10;
	const DEFAULT_IMAGE_PAGE_LEN = 10;

	/**
	 * Returns the default number of listings per page.
	 * @return int Default number of listings per page.
	 */
	public static function DEFAULT_PAGE_LEN()
	{
		return (self::DEFAULT_PAGE_LEN);
	}

	/**
	 * GalleryFilters constructor
	 * @param int $content_type_id Content type identifier, corresponds to site_section record.
	 * @param int[optional] $default_page_len Length of the pages of listings.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 */
	function __construct ( $content_type_id=null, $default_page_len=10 )
	{
		parent::__construct($content_type_id, "i");

		$this->defaultPageLength = $default_page_len;

		$this->albumId = new IntegerContentFilter("album", $this::ALBUM_PARAM, null, null, $this::COOKIE_NAME);
		$this->title = new StringContentFilter("title", "filti", '', 50, $this::COOKIE_NAME);

		/**
		 * N.B. This causes problems in filter_collection_class::preserve_in_form
		 * because when it loops through the properties this one gets inserted into
		 * the form twice.
		 */
		$this->name = &$this->title;

		$this->releaseAfter = new DateContentFilter("start date", $this::START_DATE_PARAM, '', 20, $this::COOKIE_NAME);
		$this->releaseBefore = new DateContentFilter("end date", $this::END_DATE_PARAM, '', 20, $this::COOKIE_NAME);
		$this->access = new StringContentFilter("access", $this::ACCESS_PARAM, '', 20, $this::COOKIE_NAME);
		$this->keyword = new StringContentFilter("keyword", Keyword::FILTER_PARAM, '', 50, $this::COOKIE_NAME);
		$this->slot = new IntegerContentFilter("page", $this::SLOT_PARAM, null, null, $this::COOKIE_NAME);
	}

	/**
	 * Formats the query used to retrieve filtered listings. The query string is stored in the object's $queryString property.
	 * @return string SQL query for retrieving listings data.
	 * @throws \Exception Error establishing database connection.
	 */
	public function formatListingsQuery()
	{
		$this->connectToDatabase();
		$this->query_string = 'CALL galleryFilteredSelect ('.
			$this->page->escapeSQL($this->mysqli).
			','.$this->listings_length->escapeSQL($this->mysqli).
			','.$this->escapeSQLValue($this->content_type_id).
			','.$this->albumId->escapeSQL($this->mysqli).
			','.$this->title->escapeSQL($this->mysqli).
			','.$this->releaseAfter->escapeSQL($this->mysqli).
			','.$this->releaseBefore->escapeSQL($this->mysqli).
			','.$this->access->escapeSQL($this->mysqli).
			','.$this->slot->escapeSQL($this->mysqli).
			','.$this->keyword->escapeSQL($this->mysqli).
			',@total_matches);';
		return($this->query_string);
	}

	/**
	 * Retrieve section properties.
	 * @param int|null[optional] $content_type_id Id of site section to retrieve properties for.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function getContentProperties ($content_type_id=null)
	{
		if ($content_type_id>0) {
			$this->content_properties->id->value = $content_type_id;
		}
		$this->content_properties->read();
	}

	/**
	 * Retrieves from database the uri of the page used to display details for this content type.
	 * @returns string URI of the page used to display detailed image properties.
	 * @throws \Exception Error connecting to database, or running query.
	 */
	public function getDetailsURI()
	{
		if ($this->content_type_id->id->value===null || $this->content_type_id< 1) {
			return('');
		}
		$this->connectToDatabase(); /* for the sake of real_escape_string */
		$query = "CALL getContentDetailsURI(".$this->mysqli->real_escape_string($this->content_type_id).")";
		$data = $this->fetchRecords($query);
		$this->detailsURI = $data[0]->details_uri;
		return ($this->detailsURI);
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
		if ($this->content_properties->label) {
			return($this->content_properties->pluralLabel($count));
		}
		if ($this->content_properties->label) {
			return($this->content_properties->pluralLabel($count));
		}
		return ('');
	}
}
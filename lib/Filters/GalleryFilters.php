<?php
namespace Littled\Filters;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Keyword\Keyword;
use Exception;

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
	/** @var string Details page URI */
	public $details_uri;
    /** @var string */
    protected static $cookie_key = 'alp';
    /** @var int */
    protected static $default_listings_length = 10;
    /** @var int */
    protected static $default_image_listings_length = 10;

	const ALBUM_PARAM = "pid";
	const TYPE_PARAM = "tid";
	const ACCESS_PARAM = "filac";
	const START_DATE_PARAM = "filsd";
	const END_DATE_PARAM = "filed";
	const SLOT_PARAM = "filsl";

	/**
	 * GalleryFilters constructor
	 * @throws ConfigurationUndefinedException
	 */
	function __construct ()
	{
		parent::__construct();
		$this->albumId = new IntegerContentFilter("album", $this::ALBUM_PARAM, null, null, $this::getCookieKey());
		$this->title = new StringContentFilter("title", "filti", '', 50, $this::getCookieKey());

		/**
		 * N.B. This causes problems in filter_collection_class::preserve_in_form
		 * because when it loops through the properties this one gets inserted into
		 * the form twice.
		 */
		$this->name = &$this->title;

		$this->releaseAfter = new DateContentFilter("start date", $this::START_DATE_PARAM, '', 20, $this::getCookieKey());
		$this->releaseBefore = new DateContentFilter("end date", $this::END_DATE_PARAM, '', 20, $this::getCookieKey());
		$this->access = new StringContentFilter("access", $this::ACCESS_PARAM, '', 20, $this::getCookieKey());
		$this->keyword = new StringContentFilter("keyword", Keyword::FILTER_PARAM, '', 50, $this::getCookieKey());
		$this->slot = new IntegerContentFilter("page", $this::SLOT_PARAM, null, null, $this::getCookieKey());
	}

	/**
	 * {@inheritDoc}
     * @throws NotImplementedException
	 */
	public function formatListingsQuery(): array
	{
        $content_type_id = $this::getContentTypeId();
		return array('CALL galleryFilteredSelect (?,?,?,?,?,?,?,?,?,?,@total_matches)',
            'iiiissssis',
            &$this->page->value,
            &$this->listings_length->value,
            &$content_type_id,
            &$this->albumId->value,
            &$this->title->value,
            &$this->releaseAfter->value,
            &$this->releaseBefore->value,
            &$this->access->value,
            &$this->slot->value,
            &$this->keyword->value);
	}

	/**
	 * Retrieve section properties.
	 * @param int|null[optional] $content_type_id Record id of site section to retrieve properties for.
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 */
	public function getContentProperties ($content_type_id=null)
	{
		if ($content_type_id>0) {
			$this->content_properties->id->value = $content_type_id;
		}
		$this->content_properties->read();
	}

    /**
     * Default image listings length getter
     * @return int
     */
    public function getDefaultImageListingsLength(): int
    {
        return static::$default_listings_length;
    }

	/**
	 * Retrieves from database the uri of the page used to display details for this content type.
	 * @returns string URI of the page used to display detailed image properties.
	 * @throws Exception Error connecting to database, or running query.
	 */
	public function getDetailsUri(): string
	{
		$this->connectToDatabase(); /* for the sake of real_escape_string */
		$query = "CALL getContentDetailsURI(?)";
        $content_id = $this::getContentTypeId();
		$data = $this->fetchRecords($query, 'i', $content_id);
		$this->details_uri = $data[0]->details_uri;
		return ($this->details_uri);
	}

	/**
	 * Returns an appropriate label given the value of $count if $count requires the label to be pluralized.
	 * @param int|null $count Number determining if the label is plural or not.
	 * @return string Plural form of the record label if $count is not 1.
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     */
	public function pluralLabel( ?int $count=null ): string
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
		return '';
	}

    /**
     * Default image listings length setter.
     * @param int $length
     * @return void
     */
    public static function setDefaultImageListingsLength(int $length)
    {
        static::$default_image_listings_length = $length;
    }
}
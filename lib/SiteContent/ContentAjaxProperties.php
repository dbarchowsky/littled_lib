<?php
namespace Littled\SiteContent;


use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\IntegerInput;
use Littled\Request\IntegerSelect;
use Littled\Request\StringTextarea;
use Littled\Request\StringTextField;

class ContentAjaxProperties extends SerializedContent
{
	/** @var IntegerInput Record id. */
	public $id;
	/**
	 * @var IntegerSelect Site section/content type identifier.
	 * TODO Rename this property to $content_type_id, which requires changing the name of the corresponding field
	 * in the database, and changing any SQL queries that reference that field.
	 */
	public $section_id;
	/** @var StringTextField Content label. */
	public $label;
	/** @var StringTextField Name of the variable used to pass the content type id value. */
	public $id_param;
	/** @var StringTextField URI of AJAX listings utility script. */
	public $listings_uri;
	/** @var StringTextField URI of AJAX record details utility script. */
	public $details_uri;
	/** @var StringTextField URI of AJAX record editing utility script. */
	public $edit_uri;
	/** @var StringTextField URI of AJAX record attachments upload utility script. */
	public $upload_uri;
	/** @var StringTextField URI of AJAX record deletion utility script. */
	public $delete_uri;
	/** @var StringTextField URI of AJAX record caching utility script. */
	public $cache_uri;
	/** @var StringTextField URI of AJAX listings sorting utility script. */
	public $sorting_uri;
	/** @var StringTextField URI of AJAX record keywords management utility script. */
	public $keywords_uri;
	/** @var StringTextField Path to listings template. */
    public $listings_template;
	/** @var StringTextarea Comments about the content type. */
	public $comments;
	/** @var BooleanCheckbox Flag indicating that the listings are sortable. */
	public $is_sortable;

	public static function TABLE_NAME()
	{
		return "section_operations";
	}

	function __construct()
	{
		parent::__construct();
		$this->id = new IntegerInput("Id", "capId", false);
		$this->section_id = new IntegerSelect("Content type", "capContentType", true);
		$this->label = new StringTextField("Label", "capLabel", true, '', 50);
		$this->id_param = new StringTextField("Id parameter name", "capKeyName", true, '', 50);
		$this->listings_uri = new StringTextField("Listings URI", "capListURI", false, '', 255);
		$this->details_uri = new StringTextField("Details URI", "capDetailsURI", false, '', 255);
		$this->edit_uri  = new StringTextField("Edit URI", "capEditURI", false, '', 255);
		$this->upload_uri = new StringTextField("Upload URI", "capUploadURI", false, '', 255);
		$this->delete_uri = new StringTextField("Detlete URI", "capDeleteURI", false, '', 255);
		$this->cache_uri = new StringTextField("Cache URI", "capCacheURI", false, '', 255);
		$this->sorting_uri = new StringTextField("Sorting URI", "capSortURI", false, '', 255);
		$this->keywords_uri = new StringTextField("Keywords URI", "capKeywordsURI", false, '', 255);
        $this->listings_template = new StringTextField("Listings template", "listingsTemplate", false, '', 255);
		$this->comments = new StringTextarea("Comments", "capComments", false, '', 2000);
		$this->is_sortable = new BooleanCheckbox("Is sortable", "capIsSortable", false, false);
	}

	/**
	 * Returns true/false depending on whether any data is detected in the object.
	 * @return bool TRUE if the object is holding useable data, false otherwise.
	 */
	public function hasData()
	{
		return ($this->id->value>0 || $this->section_id->value>0 || ($this->label->value) || ($this->id_param->value));
	}

	/**
	 * Returns plural label for the content type.
	 * @param int $count Number of items currently associated with the label.
	 * @param string[optional] $property_name Label to be made plural.
	 * @return string Plural label for the content type.
	 */
	public function pluralLabel($count, $property_name='label')
	{
		return(parent::pluralLabel($count, $property_name));
	}

	/**
	 * Hydrates the object based on its current content id value.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function retrieveContentProperties()
	{
		try {
			$this->testForContentType();
		}
		catch(ContentValidationException $ex) {
			return;
		}
		$query = "CALL siteSectionPropertiesSelect({$this->section_id->value})";
		$this->hydrateFromQuery($query);
	}

	/**
	 * @deprecated Use ContentAjaxProperties::retrieveContentProperties() instead.
	 * Hydrates the object based on its current content id value.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function retrieveSectionProperties()
	{
		$this->retrieveContentProperties();
	}

	/**
	 * Test for valid content type id value. Throws ContentValidationException if a value is not detected.
	 * @throws ContentValidationException
	 */
	public function testForContentType()
	{
		if ($this->section_id->value===null || $this->section_id->value < 1) {
			throw new ContentValidationException("Content type not set.");
		}
	}
}
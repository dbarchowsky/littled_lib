<?php
namespace Littled\PageContent\SiteSection;


use Littled\Exception\ContentValidationException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\FilterCollection;
use Littled\PageContent\PageContent;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\SiteContent\ContentAjaxProperties;
use Littled\SiteContent\ContentProperties;

/**
 * Class SectionContent
 * @package Littled\PageContent\SiteSection
 */
class SectionContent extends SerializedContent
{
	/** @var ContentProperties Site section properties. */
	public $contentProperties;
	/** @var string Path to listings template. */
	protected static $listingsTemplate = '';

	/**
	 * SectionContent constructor.
	 * @param int|null[optional] $id Id of record to retrieve.
	 * @param int|null[optional] $content_type_id Id of site section where this piece of content belongs.
	 */
	public function __construct($id=null, $content_type_id=null)
	{
		parent::__construct($id);
		$this->contentProperties = new ContentProperties($content_type_id);
		$this->contentProperties->id->label = "Content type";
		$this->contentProperties->id->required = true;
	}

	/**
	 * Fills the object's property values from input variable values, e.g. GET, POST, etc.
	 * @param array[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput($src=null)
	{
		$this->contentProperties->bypassCollectFromInput = true;
		parent::collectFromInput($src);
	}

	/**
	 * Deletes the Site Section record matching the object's internal ID value.
	 * @return string
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function delete()
	{
		parent::delete();
		return ("Successfully deleted ".strtolower($this->contentProperties->name->value)." record.");
	}

	/**
	 * Fetches the id of the site section that this record belongs to.
	 * @return int|null ID of the site section that this record belongs to.
	 */
	public function getContentTypeID()
	{
		if ($this->contentProperties->id->value > 0) {
			return ($this->contentProperties->id->value);
		}
		return (parent::getContentTypeID());
	}

	/**
	 * Returns the path to the listings template for this type of content.
	 * The client app will set the value of the $listingsTemplate property.
	 * @return string Path to listings template.
	 */
	public static function getListingsTemplatePath()
	{
		return (static::$listingsTemplate);
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read()
	{
		parent::read();
		$this->retrieveSectionProperties();
	}

	/**
	 * Generates markup to use to refresh listings content after inline edits have been applied to the listings data.
	 * @param FilterCollection $filters Filters to apply to listings content
	 * @return string Updated listings markup.
	 * @throws ResourceNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function refreshContentAfterEdit( &$filters )
	{
		$template = $this::getListingsTemplatePath();
		if (strlen($template) < 1) {
			$ao = new ContentAjaxProperties();
			$ao->section_id->value = $this->contentProperties->id->value;
			$ao->retrieveContentProperties();
			$template = $ao->listings_template->value;
		}
		if (strlen($template) < 1) {
			throw new ResourceNotFoundException("Listings template not available.");
		}

		$context = array(
			'content' => &$this,
			'filters' => &$filters);
		return(PageContent::loadTemplateContent($template, $context));
	}

	/**
	 * Retrieves site section properties and stores that data in object properties.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function retrieveSectionProperties()
	{
		if ($this->contentProperties->id->value===null || $this->contentProperties->id->value < 1) {
			throw new ContentValidationException("Cannot retrieve section properties. Content site section not specified within ".get_class($this).".");
		}
		$this->contentProperties->read();
	}

	/**
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function save()
	{
		if ($this->contentProperties->id->value===null || $this->contentProperties->id->value < 1) {
			throw new ContentValidationException("A content type was not specified.");
		}
		$this->contentProperties->read();
		parent::save();
	}

	/**
	 * @param string $path Path to comics listings template.
	 */
	public static function setListingsTemplatePath($path)
	{
		static::$listingsTemplate = $path;
	}

	/**
	 * Tests for a valid content type id. Throws ContentValidationException if the property value isn't current set.
	 * @throws ContentValidationException
	 */
	protected function testForContentType()
	{
		if ($this->contentProperties->id->value === null || $this->contentProperties->id->value < 0) {
			throw new ContentValidationException("Could not perform operation. A content type was not specified.");
		}
	}
}
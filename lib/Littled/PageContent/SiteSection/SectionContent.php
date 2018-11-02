<?php
namespace Littled\PageContent\SiteSection;

use Littled\Exception\ContentValidationException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\SiteContent\ContentProperties;


/**
 * Class SectionContent
 * @package Littled\PageContent\SiteSection
 */
class SectionContent extends SerializedContent
{
	/** @var ContentProperties Site section properties. */
	public $siteSection;

	/**
	 * SectionContent constructor.
	 * @param int|null[optional] $id Id of record to retrieve.
	 * @param int|null[optional] $site_section_id Id of site section where this piece of content belongs.
	 */
	public function __construct($id=null, $site_contenttype_id=null)
	{
		parent::__construct($id);
		$this->siteSection = new ContentProperties($site_contenttype_id);
		$this->siteSection->id->label = "Content type";
		$this->siteSection->id->required = true;
	}

	/**
	 * Fills the object's property values from input variable values, e.g. GET, POST, etc.
	 * @param array[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput($src=null)
	{
		$this->siteSection->bypassCollectFromInput = true;
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
		return ("Successfully deleted ".strtolower($this->siteSection->name->value)." record.");
	}

	/**
	 * Fetches the id of the site section that this record belongs to.
	 * @return int|null ID of the site section that this record belongs to.
	 */
	public function getContentTypeID()
	{
		if ($this->siteSection->id->value > 0) {
			return ($this->siteSection->id->value);
		}
		return (parent::getContentTypeID());
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
		if ($this->siteSection->id->value===null || $this->siteSection->id->value < 1) {
			throw new ContentValidationException("Cannot retrieve section properties. Content site section not specified within ".get_class($this).".");
		}
		$this->siteSection->read();
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
		if ($this->siteSection->id->value===null || $this->siteSection->id->value < 1) {
			throw new ContentValidationException("A content type was not specified.");
		}
		$this->siteSection->read();
		parent::save();
	}
}
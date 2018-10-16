<?php
namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\SiteContent\ContentAjaxProperties;
use Littled\SiteContent\ContentProperties;

/**
 * Class ContentFilters
 * @package Littled\Filters
 */
class ContentFilters extends FilterCollection
{
	/** @var ContentProperties Content properties */
	public $contentProperties;
	/** @var ContentAjaxProperties Ajax properties */
	public $ajaxProperties;
	/** @var int Content type id */
	public $contentTypeId;

	/**
	 * Intended to be implemented in inherited classes, where they return the value of the content type of that
	 * particular kind of content.
	 * @throws NotImplementedException
	 */
	public static function CONTENT_TYPE_ID()
	{
		throw new NotImplementedException(__METHOD__." not implemented.");
	}

	/**
	 * ContentFilters constructor.
	 * @param string $param_prefix
	 * @throws ConfigurationUndefinedException Database connections properties not set.
	 * @throws \Exception Error retrieving content section properties.
	 */
	function __construct( $param_prefix='' )
	{
		parent::__construct( $param_prefix );
		$this->contentTypeId = $this->CONTENT_TYPE_ID();
		$this->contentProperties = new ContentProperties($this->contentTypeId);
		$this->ajaxProperties = new ContentAjaxProperties();
		$this->ajaxProperties->section_id->value = $this->contentTypeId;
		$this->contentProperties->read();
		$this->ajaxProperties->retrieveSectionProperties();
	}

	/**
	 * Placeholder for method intended to be implemented by derived classes.
	 * Format a query that will be used to retrieve listings recordset.
	 * @return string
	 * @throws NotImplementedException
	 */
	protected function formatListingsQuery()
	{
		throw new NotImplementedException(__METHOD__." not implemented.");
	}

	/**
	 * Returns object's content type id value
	 * @return int
	 */
	public function getContentTypeId()
	{
		return ($this->contentTypeId);
	}

	/**
	 * Retrieves listings using sql in $query argument. Stores the total
	 * number of matches and updates internal values of total number of pages
	 * and current page number.
	 * @return array List of generic objects containing the records returned by the query.
	 * @throws \Exception Error running query.
	 */
	public function retrieveListings()
	{
		$this->formatListingsQuery();
		$data = $this->fetchRecordsNonExhaustive($this->queryString);
		$this->getSprocPageCount();
		return ($data);
	}
}
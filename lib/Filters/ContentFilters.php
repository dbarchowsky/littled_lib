<?php
namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\SiteContent\ContentAjaxProperties;
use Littled\SiteContent\ContentProperties;
use Exception;
use mysqli_result;

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
	/** @var int $contentTypeID Pointer to contentProperties->id->value for convenience */
	public $contentTypeID;

	/**
	 * ContentFilters constructor.
	 * @param int $content_type_id Content type identifier.
	 * @throws ConfigurationUndefinedException Database connections properties not set.
	 * @throws Exception Error retrieving content section properties.
	 */
	function __construct( int $content_type_id )
	{
		parent::__construct();
		$this->contentProperties = new ContentProperties($content_type_id);
		$this->contentTypeID = &$this->contentProperties->id->value;
		$this->ajaxProperties = new ContentAjaxProperties();
		$this->ajaxProperties->section_id->value = $this->contentTypeID;
		$this->contentProperties->read();
		$this->ajaxProperties->retrieveContentProperties();
	}

	/**
	 * Placeholder for method intended to be implemented by derived classes.
	 * Format a query that will be used to retrieve listings recordset.
	 * @return string SQL query
	 * @throws NotImplementedException
	 */
	protected function formatListingsQuery(): string
	{
		throw new NotImplementedException(__METHOD__." not implemented.");
	}

	/**
	 * Returns object's content type id value
	 * @return int
	 */
	public function getContentTypeId(): int
	{
		return ($this->contentTypeID);
	}
}
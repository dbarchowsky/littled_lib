<?php
namespace Littled\Filters;

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
	 * @return int Content type id
	 * @throws NotImplementedException
	 */
	public static function CONTENT_TYPE_ID()
	{
		throw new NotImplementedException(__METHOD__." not implemented.");
	}

	/**
	 * ContentFilters constructor.
	 *
	 * @param string $param_prefix
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
	 * @param string $query SQL query to execute.
	 * @return array List of generic objects containing the records returned by the query.
	 */
	public function retrieveListings($query='')
	{
		$this->connectToDatabase();
		if ($query=='') {
			$query = $this->formatListingsQuery();
		}
		$data = $this->fetchRecords($query);
		$this->getSprocPageCount();
		return ($data);
	}
}
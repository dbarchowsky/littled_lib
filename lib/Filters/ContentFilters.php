<?php
namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\SiteContent\ContentAjaxProperties;
use Littled\SiteContent\ContentProperties;
use Exception;

/**
 * Class ContentFilters
 * @package Littled\Filters
 */
class ContentFilters extends FilterCollection
{
	/** @var ContentProperties Content properties */
	public $content_properties;
	/** @var ContentAjaxProperties Ajax properties */
	public $ajax_properties;
	/** @var int $content_type_id Pointer to contentProperties->id->value for convenience */
	public $content_type_id;

	/**
	 * ContentFilters constructor.
	 * @param int $content_type_id Content type identifier.
	 * @throws ConfigurationUndefinedException Database connections properties not set.
	 * @throws Exception Error retrieving content section properties.
	 */
	function __construct( int $content_type_id )
	{
		parent::__construct();
		$this->content_properties = new ContentProperties($content_type_id);
		$this->content_type_id = &$this->content_properties->id->value;
		$this->ajax_properties = new ContentAjaxProperties();
		$this->ajax_properties->section_id->value = $this->content_type_id;
		$this->content_properties->read();
		$this->ajax_properties->retrieveContentProperties();
	}

	/**
	 * Returns object's content type id value
	 * @return int
	 */
	public function getContentTypeId(): int
	{
		return ($this->content_type_id);
	}
}
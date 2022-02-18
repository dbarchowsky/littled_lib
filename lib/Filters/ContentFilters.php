<?php
namespace Littled\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Ajax\ContentAjaxProperties;
use Littled\PageContent\SiteSection\ContentProperties;
use Exception;

/**
 * Class ContentFilters
 * @package Littled\Filters
 */
class ContentFilters extends FilterCollection
{
	/** @var string */
	public const NEXT_OP_ADD = 'add';
	/** @var string */
	public const NEXT_OP_VIEW = 'view';
	/** @var string */
	public const NEXT_OP_ADD_IMAGE = 'add_img';
	/** @var string */
	public const NEXT_OP_PREVIOUS = 'prev';
	/** @var string */
	public const NEXT_OP_LIST = 'list';

	/** @var ContentProperties Content properties */
	public ContentProperties $content_properties;
	/** @var ContentAjaxProperties Ajax properties */
	public ContentAjaxProperties $ajax_properties;
	/** @var int */
	protected static int $content_type_id;

	/**
	 * ContentFilters constructor.
	 * @throws ConfigurationUndefinedException Database connections properties not set.
	 * @throws Exception Error retrieving content section properties.
	 */
	function __construct()
	{
		parent::__construct();
		$this->content_properties = new ContentProperties(self::getContentTypeId());
		$this->ajax_properties = new ContentAjaxProperties();

		$this->content_properties->read();
        $this->ajax_properties->section_id->value = $this->getContentTypeId();
		$this->ajax_properties->retrieveContentProperties();
	}

	/**
	 * Content type id getter.
	 * @return int
     * @throws NotImplementedException
	 */
	public static function getContentTypeId(): int
	{
		if(!static::$content_type_id) {
            throw new NotImplementedException('Content type id not set in '.get_called_class().'.');
        }
        return static::$content_type_id;
	}

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function pluralLabel(): string
    {
        return $this->content_properties->pluralLabel($this->record_count);
    }

    /**
     * Content type id setter.
     * @param int $content_id
     * @return void
     */
    public static function setContentTypeId(int $content_id)
    {
        static::$content_type_id = $content_id;
    }
}
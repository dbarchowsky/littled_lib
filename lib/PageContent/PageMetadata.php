<?php
namespace Littled\PageContent;


/**
 * Class PageMetadata
 * Site metadata properties.
 * @package Littled\PageContent
 */
class PageMetadata
{
	/** @var string Page description inserted into the page metadata for SEO. */
	public $description = "";
	/** @var array List of keywords to be inserted into the page metadata for SEO. */
	public $keywords = array();
	/** @var string Meta data title displayed in the browser title bar for SEO. */
	public $metaTitle = "";
	/** @var string Core name for the site. */
	public $siteLabel = "";
	/** @var string Page title suitable for displaying in the page content. */
	public $title = "";
}

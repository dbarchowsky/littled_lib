<?php
namespace Littled\PageContent;


class PageMetadata
{
	/** @var string Core name for the site. */
	public $site_label = "";
	/** @var string Page title suitable for displaying in the page content. */
	public $title = "";
	/** @var string Meta data title displayed in the browser title bar for SEO. */
	public $meta_title = "";
	/** @var string Page description inserted into the page metadata for SEO. */
	public $description = "";
	/** @var array List of keywords to be inserted into the page metadata for SEO. */
	public $keywords = array();
}

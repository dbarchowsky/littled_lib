<?php
namespace Littled\Cache;


use Littled\Database\MySQLConnection;


/**
 * Class ContentCache
 * Handles handling the caching of page content in files stored on disk.
 * @package Littled\Cache
 */
class ContentCache extends MySQLConnection
{
	/**
	 * @param \Littled\PageContent\SiteSection\ContentProperties $site_section Object containing content type information
	 * for the page being cached.
	 */
	public static function updateKeywords($site_section)
	{
		/* Stub method. The logic of this function to be defined in inherited classes. */
		return;
	}
}
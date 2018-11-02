<?php
namespace Littled\Cache;


use Littled\Database\MySQLConnection;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\SiteContent\ContentProperties;


/**
 * Class ContentCache
 * Handles handling the caching of page content in files stored on disk.
 * @package Littled\Cache
 */
class ContentCache extends MySQLConnection
{
	/**
	 * Updates parent link to child based on content type.
	 * @param SectionContent $content Content type object.
	 */
	public static function setInitialProperties( &$content )
	{
		switch($content->siteSection->id->value)
		{
			/* define content type-specific handlers here */
			/* break; */

			default:
				/* content type not handled */
				break;
		}
	}

	/**
	 * Updates content based on content type.
	 * @param ContentProperties $content_properties object containing content properties
	 * @param SectionContent|null[optional] $content Either an object representing the content to be updated, or an id of the record to use to update the content cache.
	 * @return string Message describing the results of the operation.
	 */
	public static function updateCache( &$content_properties, &$content=null )
	{
		$status = "";
		switch ($content_properties->id->value) {
			default:
				if ($content instanceof SectionContent) {
					$status = "Unsupported content type: \"{$content->siteSection->name->value}\".";
				}
				break;
		}
		return ($status);
	}

	/**
	 * @param int $parent_id
	 * @param int $content_type_id
	 * @return array
	 */
	public static function updateKeywords( $parent_id, $content_type_id )
	{
		/* Stub method. The logic of this function to be defined in inherited classes. */
		return (array($parent_id, $content_type_id));
	}
}
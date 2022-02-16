<?php
namespace Littled\Cache;


use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\Filters\ContentFilters;
use Littled\Filters\FilterCollection;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\PageContent\SiteSection\ContentProperties;


/**
 * Class ContentCache
 * Handles handling the caching of page content in files stored on disk.
 * @package Littled\Cache
 */
abstract class ContentCache extends MySQLConnection
{
	/**
	 * Updates parent link to child based on content type.
	 * @param SectionContent $content Content type object.
	 */
	abstract public static function setInitialProperties( SectionContent $content ): void;

	/**
	 * Updates content based on content type.
	 * @param ContentProperties $content_properties object containing content properties
	 * @param ?SectionContent $content (Optional) Either an object representing the content to be updated, or an id of the record to use to update the content cache.
	 * @return string Message describing the results of the operation.
	 */
	abstract public static function updateCache( ContentProperties $content_properties, ?SectionContent $content=null ): string;

	/**
	 * @param int $parent_id
	 * @param ContentProperties $content_properties
	 * @return array
	 */
	abstract public static function updateKeywords( int $parent_id, ContentProperties $content_properties): array;
}
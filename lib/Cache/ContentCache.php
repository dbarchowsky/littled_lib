<?php
namespace Littled\Cache;


use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\Filters\FilterCollection;
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
		switch($content->contentProperties->id->value)
		{
			/* define content type-specific handlers here */
			/* break; */

			default:
				/* content type not handled */
				break;
		}
	}

	/**
	 * Retrieve content and filters class names, and create instances of each.
	 * @param int $content_type_id Content type identifier used to retrieve class types.
	 * @param SectionContent $content Pointer to content object that will be created.
	 * @param FilterCollection $filters Pointer to filters object that will be created.
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function setContentAndFilters( $content_type_id, &$content, &$filters )
	{
		$conn = new MySQLConnection();
		$query = "CALL siteSectionClassesSelect({$content_type_id})";
		$data = $conn->fetchRecords($query);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Content properties record not found.");
		}
		if ($data[0]->content_class) {
			$class = $data[0]->content_class;
			if (!class_exists($class)) {
				throw new ContentValidationException("\"{$data[0]->content_class}\" class not recognized.");
			}
			$content = new $class();
		}
		if ($data[0]->filters_class) {
			$class = $data[0]->filters_class;
			if (!class_exists($class)) {
				throw new ContentValidationException("\"{$data[0]->filters_class}\" class not recognized.");
			}
			$filters = new $class();
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
					$status = "Unsupported content type: \"{$content->contentProperties->name->value}\".";
				}
				break;
		}
		return ($status);
	}

	/**
	 * @param int $parent_id
	 * @param ContentProperties $content_properties
	 * @return array
	 */
	public static function updateKeywords( $parent_id, $content_properties )
	{
		/* Stub method. The logic of this function to be defined in inherited classes. */
		return (array($parent_id, $content_properties->id->value));
	}
}
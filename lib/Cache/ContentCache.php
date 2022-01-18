<?php
namespace Littled\Cache;


use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
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
	public static function setInitialProperties( SectionContent $content ): void
	{
		switch($content->content_properties->id->value)
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
	 * @throws InvalidQueryException
	 */
	public static function setContentAndFilters( int $content_type_id, SectionContent &$content, FilterCollection &$filters ): void
	{
		$conn = new MySQLConnection();
		$query = "CALL siteSectionClassesSelect($content_type_id)";
		$data = $conn->fetchRecords($query);
		if (count($data) < 1) {
			throw new RecordNotFoundException("Content properties record not found.");
		}
		self::setObject($data[0]->content_class,$content);
		self::setObject($data[0]->filters_class,$filters);
	}

    /**
     * Uses a content type id to look up the class name of the filters class linked to that content type. Creates an
     * instance of the filters class and assigns it to the $filters variable.
     * @param int $content_type_id Content type id,
     * @param object $filters Pointer to variable that will be the instance of the filters class.
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws InvalidQueryException
     */
	public static function setFilters( int $content_type_id, &$filters ): void
    {
        $conn = new MySQLConnection();
        $query = "CALL siteSectionClassesSelect($content_type_id)";
        $data = $conn->fetchRecords($query);
        if (count($data) < 1) {
            throw new RecordNotFoundException("Content properties record not found.");
        }
        self::setObject($data[0]->filters_class, $filters);
    }

    /**
     * Creates new instance of $class_name and assigns it to $obj.
     * @param string $class_name
     * @param object $obj Pointer to variable that will be the instance of the class.
     * @throws ContentValidationException
     */
    protected static function setObject( string $class_name, &$obj )
    {
        if ($class_name) {
            if (!class_exists($class_name)) {
                throw new ContentValidationException("\"$class_name\" class not recognized.");
            }
            $obj = new $class_name();
        }
    }

	/**
	 * Updates content based on content type.
	 * @param ContentProperties $content_properties object containing content properties
	 * @param ?SectionContent $content (Optional) Either an object representing the content to be updated, or an id of the record to use to update the content cache.
	 * @return string Message describing the results of the operation.
	 */
	public static function updateCache( ContentProperties $content_properties, ?SectionContent $content=null ): string
	{
		$status = "";
		switch ($content_properties->id->value) {
			default:
				if ($content instanceof SectionContent) {
					$status = "Unsupported content type: \"{$content->content_properties->name->value}\".";
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
	public static function updateKeywords( int $parent_id, ContentProperties $content_properties): array
	{
		/* Stub method. The logic of this function to be defined in inherited classes. */
		return (array($parent_id, $content_properties->id->value));
	}
}
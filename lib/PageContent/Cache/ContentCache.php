<?php
namespace Littled\PageContent\Cache;

use Littled\Ajax\AjaxPage;
use Littled\Ajax\JSONRecordResponse;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Filters\ContentFilters;
use Littled\Filters\FilterCollection;
use Littled\Log\Debug;
use Littled\PageContent\ContentController;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use Exception;
use ReflectionClass;

/**
 * Updates site cache after content updates.
 */
abstract class ContentCache extends MySQLConnection
{
    /** @var string */
    protected static $controller_class = ContentController::class;

    /**
	 * class constructor
	 */
	function __construct (  )
    {
        parent::__construct();
	}

    /**
     * Controller class getter
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getControllerClass(): string
    {
        if (ContentController::class === static::$controller_class) {
            throw new ConfigurationUndefinedException('Controller class not set.');
        }
        return static::$controller_class;
    }

    /**
     * Returns the path to the template used to render the markup returned to the client and sets
     * any necessary state property values within the $page object.
     * @param AjaxPage $page Page content object used to return markup to client.
     * @param string $operation Token representing operation being requested by the client.
     * @return string
     */
    protected abstract static function loadJsonTemplatePath(AjaxPage $page, string $operation ): string;

    /**
     * Returns the page to the template used to render markup returned to the client after a record  has been edited.
     * @param SectionContent $content
     * @return string
     */
    protected abstract static function loadPostEditTemplatePath(SerializedContent $content): string;

    /**
     * Refresh content after performing an AJAX edit on a record
     * @param SerializedContent $content Object representing the content record that was updated.
     * @param ContentFilters $filters Object containing pages filters to be used to return refreshed page content.
     * @param JSONRecordResponse $json Object used to return page content after AJAX call.
     * @throws Exception
     */
    public static function refreshContentAfterEdit (
        SerializedContent &$content,
        ContentFilters &$filters,
        JSONRecordResponse $json
    )
    {
        $template_path = static::loadPostEditTemplatePath($content);
        if ($template_path) {
            $json->loadContentFromTemplate($template_path, array(
                'content' => &$content,
                'filters' => &$filters
            ));
        }
    }

    /**
     * Tailored for image updates, refreshes page content after performing an (ajax) update to an individual record.
     * @param SectionContent $content Object representing the content record that was updated.
     * @param FilterCollection $filters Object containing pages filters to be used to return refreshed page content.
     * @param JSONRecordResponse $json Object used to return page content after AJAX call.
     * @return void
     * @throws Exception
     */
    public abstract static function refreshContentAfterImageEdit (
        SectionContent &$content,
        FilterCollection &$filters,
        JSONRecordResponse $json
    ): void;

    /**
     * Controller class setter.
     * @param string $class_name
     * @return void
     * @throws InvalidTypeException
     */
    public static function setControllerClass(string $class_name)
    {
        $o = new $class_name;
        if (!$o instanceof ContentController) {
            throw new InvalidTypeException(Debug::getShortMethodName().' invalid controller class '.$class_name.'.');
        }
        unset($o);
        static::$controller_class = $class_name;
    }

    /**
	 * Updates parent link to child based on content type.
	 * @param SectionContent $content Content type object.
     */
	public abstract static function setInitialProperties(SectionContent $content);

    /**
     * Updates content based on content type.
     * @param ContentProperties $content_properties object containing content properties
     * @param ?SectionContent $content (Optional) Either an object representing the content to be updated, or an id of the record to use to update the content cache.
     * @param bool $update_parent_cache (Optional) flag to update parent records.
     * @return string Message indicating the results of the operation.
     */
    public abstract static function updateCache (
        ContentProperties $content_properties,
        ?SectionContent $content=null,
        bool $update_parent_cache=false ): string;

    /**
     * Updates keywords for a particular content type.
     * @param int $id Record id of the record to update.
     * @param int $content_id Content type id of the record being updated.
     * @throws Exception
     */
    public static function updateKeywords(int $id, int $content_id )
    {
        $_content = null;

        static::updateKeywordsByType($content_id);

        if (is_object($_content)) {
            $_content->id->value = $id;
            $_content->update_fulltext_keywords();
            unset($_content);
        }
    }

    /**
     * Update keyword logic by content type.
     * @param int $content_id
     * @return void
     */
    protected abstract static function updateKeywordsByType(int $content_id): void;
}

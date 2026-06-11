<?php

namespace Littled\PageContent\Cache;

use Littled\API\APIRoute;
use Littled\API\JSONRecordResponse;
use Littled\Database\MySQLConnection;
use Littled\Exception\NotImplementedException;
use Littled\Filters\FilterCollection;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use Exception;


/**
 * Updates site cache after content updates.
 */
abstract class ContentCache extends MySQLConnection
{
    /**
     * Returns the path to the template used to render the markup returned to the client and sets
     * any necessary state property values within the $page object.
     * @param APIRoute $page Page content object used to return markup to a client.
     * @param string $operation Token representing the operation being requested by the client.
     * @return string
     */
    protected abstract static function loadJsonTemplatePath(APIRoute $page, string $operation): string;

    /**
     * Tailored for image updates, refreshes page content after performing an (ajax) update to an individual record.
     * @param SectionContent $content Object representing the content record that was updated.
     * @param FilterCollection $filters Object containing pages filters to be used to return refreshed page content.
     * @param JSONRecordResponse $json Object used to return page content after AJAX call.
     * @return void
     * @throws Exception
     */
    public abstract static function refreshContentAfterImageEdit(
        SectionContent     $content,
        FilterCollection   $filters,
        JSONRecordResponse $json
    ): void;

    /**
     * Updates a parent link to a child based on content type.
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
    public abstract static function updateCache(
        ContentProperties $content_properties,
        ?SectionContent   $content = null,
        bool              $update_parent_cache = false): string;

    /**
     * Updates keywords for a particular content type.
     * @param int $id Record id of the record to update.
     * @param int $content_id Content type id of the record being updated.
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public static function updateKeywords(int $id, int $content_id): void
    {
        static::updateKeywordsByType($content_id);
    }

    /**
     * Update keyword logic by content type.
     * @param int $content_id
     * @return void
     * @throws NotImplementedException
     * @noinspection PhpUnusedParameterInspection
     */
    protected static function updateKeywordsByType(int $content_id): void
    {
        throw new NotImplementedException(Log::getShortMethodName() . ' not implemented.');
    }
}

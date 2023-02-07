<?php
namespace Littled\Tests\TestHarness\PageContent\Cache;

use Littled\Ajax\AjaxPage;
use Littled\Ajax\JSONRecordResponse;
use Littled\Filters\FilterCollection;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;


class ContentCacheTestHarness extends ContentCache
{
    protected static function loadJsonTemplatePath(AjaxPage $page, string $operation): string
    {
        return 'Abstract method placeholder.';
    }

    /**
     * @param int $content_id
     * @return void
     */
    protected static function updateKeywordsByType(int $content_id): void
    {
        // TODO: Implement updateKeywordsByType() method.
    }

    /**
     * @param ContentProperties $content_properties
     * @param SectionContent|null $content
     * @param bool $update_parent_cache
     * @return string
     */
    public static function updateCache(ContentProperties $content_properties, ?SectionContent $content = null, bool $update_parent_cache = false): string
    {
        return '';
    }

    /**
     * @param SectionContent $content
     * @param FilterCollection $filters
     * @param JSONRecordResponse $json
     * @return void
     */
    public static function refreshContentAfterImageEdit(SectionContent &$content, FilterCollection &$filters, JSONRecordResponse $json): void
    {
        // TODO: Implement refreshContentAfterImageEdit() method.
    }

    /**
     * @param SectionContent $content
     * @return mixed
     */
    public static function setInitialProperties(SectionContent $content)
    {
        return null;
    }
}
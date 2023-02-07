<?php
namespace Littled\Tests\TestHarness\PageContent\SiteSection;

use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\ContentRoute;


class ContentPropertiesTestHarness extends ContentProperties
{
    public function publicNewRouteInstance(
        ?int $record_id=null,
        ?int $site_section_id=null,
        string $operation='',
        string $route='',
        string $url=''
    ): ContentRoute
    {
        return $this->newRouteInstance($record_id, $site_section_id, $operation, $route, $url);
    }
}
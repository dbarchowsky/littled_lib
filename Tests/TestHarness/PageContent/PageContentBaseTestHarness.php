<?php
namespace Littled\Tests\TestHarness\PageContent;

use Littled\PageContent\PageContentBase;


class PageContentBaseTestHarness extends PageContentBase
{
    public function sendResponse(string $template_path = '', ?array $context = null)
    {
        // TODO: Implement sendResponse() method.
    }

    public function render(?array $context = null)
    {
        // TODO: Implement render() method.
    }

    public function getTemplateContext(): array
    {
        // TODO: Implement getTemplateContext() method.
        return [];
    }

    public function getContentLabel(): string
    {
        // TODO: Implement getContentLabel() method.
        return '';
    }
}
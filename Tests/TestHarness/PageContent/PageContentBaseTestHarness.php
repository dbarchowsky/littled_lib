<?php
namespace Littled\Tests\TestHarness\PageContent;

use Littled\PageContent\PageContentBase;


class PageContentBaseTestHarness extends PageContentBase
{
    protected static array $route_parts = ['route-base', 'route-sub'];
    /** @var string Override parent to make property public for tests */
    public string $query_string='';

	public function collectRequestData(?array $src = null)
	{
		// stub
	}

    public function getContentLabel(): string
    {
	    // stub
        return '';
    }

	public function getTemplateContext(): array
	{
		// stub
		return [];
	}

	public function processRequest()
	{
		// stub
	}

	public function render(?array $context = null)
	{
		// stub
	}

	public function sendResponse(string $template_path = '', ?array $context = null)
	{
		// stub
	}
}
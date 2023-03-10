<?php
namespace Littled\Tests\TestHarness\PageContent;

use Littled\PageContent\PageContent;


class PageContentTestHarness extends PageContent
{
	public function collectRequestData(?array $src = null)
	{
		// stub
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateContext(): array
	{
		// stub
		return [];
	}

	public function processRequest(): PageContent
	{
		return $this;
	}

	public function setPageState()
	{
		// stub
	}
}
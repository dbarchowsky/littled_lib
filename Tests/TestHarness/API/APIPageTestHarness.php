<?php
namespace Littled\Tests\TestHarness\API;

use Littled\API\APIPage;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\PageContent\PageContent;


class APIPageTestHarness extends APIPage
{
    public static function publicGetAjaxClientRequestData(): ?array
    {
        return parent::getAjaxClientRequestData();
    }

    /**
     * @return PageContent
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     * @throws InvalidQueryException
     */
    public function newRoutedPageContentTemplateInstance(): PageContent
    {
        return $this->newRoutedPageContentInstance();
    }

	public function collectAndLoadJsonContent()
	{
		// TODO: Implement collectAndLoadJsonContent() method.
	}

	public function hasContentPropertiesObject(): bool
	{
		// TODO: Implement hasContentPropertiesObject() method.
        return false;
	}

	public function retrieveContentData()
	{
		// TODO: Implement retrieveContentData() method.
	}

	protected function retrieveCoreContentProperties()
	{
		// TODO: Implement retrieveCoreContentProperties() method.
	}

	public function setContentTypeId(int $content_id)
	{
		// TODO: Implement setContentTypeId() method.
	}
}
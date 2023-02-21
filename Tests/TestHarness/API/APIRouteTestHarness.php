<?php
namespace Littled\Tests\TestHarness\API;

use Littled\API\APIRoute;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\PageContent;


class APIRouteTestHarness extends APIRoute
{
    public function collectAndLoadJsonContent()
    {
        // TODO: Implement collectAndLoadJsonContent() method.
    }

	public function hasContentPropertiesObject(): bool
	{
		// TODO: Implement hasContentPropertiesObject() method.
        return false;
	}

    /**
     * @inheritDoc
     * Override parent to provide public interface for tests.
     */
    public function initializeFiltersObject(?int $content_type_id = null)
    {
        parent::initializeFiltersObject($content_type_id);
    }

    /**
     * @return PageContent
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     * @throws InvalidQueryException|RecordNotFoundException
     */
    public function newRoutedPageContentTemplateInstance(): PageContent
    {
        return $this->newRoutedPageContentInstance();
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
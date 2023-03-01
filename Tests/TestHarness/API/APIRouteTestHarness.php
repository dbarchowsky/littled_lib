<?php
namespace Littled\Tests\TestHarness\API;

use Littled\API\APIRoute;


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
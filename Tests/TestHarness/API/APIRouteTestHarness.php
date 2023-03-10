<?php
namespace Littled\Tests\TestHarness\API;

use Littled\API\APIRoute;


class APIRouteTestHarness extends APIRoute
{
    public function collectAndLoadJsonContent()
    {
	    // stub
    }

	public function hasContentPropertiesObject(): bool
	{
		// stub
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
		// stub
	}

	protected function retrieveCoreContentProperties()
	{
		// stub
	}

	public function setContentTypeId(int $content_id)
	{
		// stub
	}
}
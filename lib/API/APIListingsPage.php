<?php
namespace Littled\API;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Validation\Validation;

class APIListingsPage extends APIPage
{
	/**
	 * @inheritDoc
	 */
	public function collectAndLoadJsonContent()
	{
		/** retrieve filters object if needed */
		if (!isset($this->filters)) {
			$this->initializeFiltersObject();
		}
		$this->retrieveContentData();
		$this->loadTemplateContent();
	}

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException|NotImplementedException
     */
	public function collectRequestData(?array $src = null)
	{
        parent::collectRequestData($src);
        $content_type_id = Validation::collectIntegerRequestVar(LittledGlobals::CONTENT_TYPE_KEY, null, $src);
        if (!isset($this->filters)) {
            if ($content_type_id===null || $content_type_id < 1) {
                throw new ConfigurationUndefinedException('Content type not provided.');
            }
            $this->initializeFiltersObject($content_type_id);
        }
		$this->filters->collectFilterValues(true, [], $src);
	}

    /**
     * @inheritDoc
     */
    public function getContentProperties(): ContentProperties
    {
        if (isset($this->filters)) {
            return $this->filters->content_properties;
        }
        return parent::getContentProperties();
    }

    /**
	 * @inheritDoc
	 */
	public function hasContentPropertiesObject(): bool
	{
		return isset($this->filters);
	}

    /**
     * Assigns a ContentFilters instance to the $filters property.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected function initializeFiltersObject(?int $content_type_id=null)
    {
        $this->filters = call_user_func(
            [static::getControllerClass(), 'getContentFiltersObject'],
            $content_type_id ?: $this->getContentTypeId());
    }

    /**
     * @inheritDoc
     */
    public function retrieveContentData()
    {
        $this->collectRequestData();
    }

    /**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	protected function retrieveCoreContentProperties()
	{
		$this->filters->content_properties->read();
	}

    /**
     * @inheritDoc
     */
    public function sendResponse(string $template_path = '', ?array $context = null)
    {
        // TODO: Implement sendResponse() method.
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    public function setContentTypeId(int $content_id)
    {
        if (!isset($filters)) {
            $this->initializeFiltersObject($content_id);
        }
        $this->filters->content_properties->id->setInputValue($content_id);
    }
}
<?php
namespace Littled\API;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Validation\Validation;


class APIRecordRoute extends APIRoute
{
	public SectionContent $content;

	/**
	 * Convenience routine that will collect the content id from POST
	 * data using first the content object's internal id parameter, and then if
	 * that value is unavailable, a default id parameter ("id").
	 * @param ?array $src Optional array of variables to use instead of POST data.
	 */
	public function collectContentID( ?array $src=null )
	{
		$this->content->id->collectRequestData($src);
		if ($this->content->id->value===null && $this->content->id->key != LittledGlobals::ID_KEY) {
			$this->content->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY, null, $src);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function collectContentProperties(string $key=LittledGlobals::CONTENT_TYPE_KEY)
	{
		parent::collectContentProperties($key);
		$this->collectContentID();
	}

	/**
	 * @inheritDoc
	 * @throws ConfigurationUndefinedException|ContentValidationException
	 */
	public function collectRequestData( ?array $src=null )
	{
		parent::collectRequestData();
		if (!isset($this->content)) {
			$this->initializeContentObject(null, $src);
		}
		$this->content->collectRequestData($src);
		$this->retrieveContentProperties();
	}

	public function getContentProperties(): ContentProperties
	{
		if ($this->hasContentPropertiesObject()) {
			return $this->content->content_properties;
		}
		return parent::getContentProperties();
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateContext(): array
	{
		return array_merge(
			parent::getTemplateContext(),
			array('content' => (isset($this->content))?($this->content):(null)));
	}

	/**
	 * Checks the "class" variable of the POST data and uses it to instantiate an object to be used to manipulate the record content.
     * @param ?int $content_id Optional content type id to use to retrieve content instance.
     * @param ?array $src Optional array of variables to use instead of POST data.
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 */
	public function initializeContentObject( ?int $content_id=null, ?array $src=null )
	{
        if (!$content_id) {
            if ($src === null) {
                // ignore GET request data
                $src = &$_POST;
            }
            $content_id = Validation::collectIntegerRequestVar(LittledGlobals::CONTENT_TYPE_KEY, null, $src);
            if (!$content_id) {
                throw new ContentValidationException("Content type not provided.");
            }
        }
		$this->content = call_user_func([static::getControllerClass(), 'getContentObject'], $content_id);
	}

	/**
	 * @inheritDoc
	 */
	public function hasContentPropertiesObject(): bool
	{
		return isset($this->content);
	}

	/**
	 * Retrieves content data from the database
	 * @return void
	 * @throws ConfigurationUndefinedException
	 */
	public function retrieveContentData()
	{
		if(!$this->hasContentPropertiesObject()) {
			return;
		}
		if ($this->content->id->value===null || $this->content->id->value < 1) {
			throw new ConfigurationUndefinedException('A record id was not provided.');
		}
		call_user_func_array([$this::getControllerClass(), 'retrieveContentDataByType'], array($this->content));
	}

	/**
	 * Loads the content object and uses the internal record id property value to hydrate the object's property value from the database.
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 */
	public function retrieveContentObjectAndData()
	{
        $ajax_data = static::getAjaxRequestData();
		$this->initializeContentObject(null, $ajax_data);
        $this->content->id->collectRequestData($ajax_data);
		$this->retrieveContentData();
	}

	/**
	 * Hydrates the content properties object by retrieving data from the database.
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws ConnectionException
	 * @throws RecordNotFoundException
	 */
	public function retrieveCoreContentProperties()
	{
		if (!$this->hasContentPropertiesObject()) {
			throw new ConfigurationUndefinedException('Content object not available.');
		}
		$this->content->content_properties->read();
	}

	/**
	 * Renders a page content template based on the current content filter values and stores the markup in the object's $json property.
	 * @throws ResourceNotFoundException|NotImplementedException
	 */
	public function retrievePageContent()
	{
		$this->filters->collectFilterValues();
		$this->json->content->value = $this->content->refreshContentAfterEdit($this->filters);
	}

	/**
	 * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     */
	public function setContentTypeId(int $content_id)
	{
		if (!$this->hasContentPropertiesObject()) {
            $this->initializeContentObject($content_id);
        }
        $this->content->content_properties->id->setInputValue($content_id);
	}
}
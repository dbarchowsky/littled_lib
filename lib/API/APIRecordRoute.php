<?php

namespace Littled\API;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
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
     * @return $this
     */
    public function collectContentID(?array $src = null): APIRecordRoute
    {
        // first, try extracting the record id from the api route
        $rp_id = $this->lookupRecordIdRoutePart();
        if ($rp_id) {
            $this->content->id->value = Validation::parseNumeric($rp_id);
            if ($this->content->id->value > 0) {
                return $this;
            }
        }

        // next, collect record id value from ajax/post data using input property's internal parameter name
        $this->content->id->collectRequestData($src);
        if ($this->content->id->value > 0) {
            return $this;
        }

        // if the internal key value doesn't hold anything, and it's non-default, try looking up the record id value
        // in ajax/post data using the default record id key
        if ($this->content->id->key != LittledGlobals::ID_KEY) {
            $this->content->id->value = Validation::collectIntegerRequestVar(LittledGlobals::ID_KEY, null, $src);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function collectContentProperties(string $key = LittledGlobals::CONTENT_TYPE_KEY)
    {
        parent::collectContentProperties($key);
        $this->collectContentID();
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException|ContentValidationException
     */
    public function collectRequestData(?array $src = null)
    {
        parent::collectRequestData();
        if (!isset($this->content)) {
            $this->initializeContentObject(null, $src);
        }
        $this->content->collectRequestData($src);
        $this->retrieveContentProperties();
    }

    /**
     * @inheritDoc
     */
    public function getContentProperties(): ContentProperties
    {
        if ($this->hasContentPropertiesObject()) {
            return $this->content->content_properties;
        }
        return parent::getContentProperties();
    }

    /**
     * Returns singular record id value if that is what is currently stored in the $record_ids property.
     * Null is returned if $record_ids is storing no values or multiple values.
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        return ($this->content->id->value === false ? null : $this->content->id->value);
    }

    /**
     * @inheritDoc
     */
    public function getTemplateContext(): array
    {
        return array_merge(
            parent::getTemplateContext(),
            array('content' => (isset($this->content)) ? ($this->content) : (null)));
    }

    /**
     * Checks the "class" variable of the POST data and uses it to instantiate an object to be used to manipulate the record content.
     * @param ?int $content_id Optional content type id to use to retrieve content instance.
     * @param ?array $src Optional array of variables to use instead of POST data.
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @return $this
     */
    public function initializeContentObject(?int $content_id = null, ?array $src = null): APIRecordRoute
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
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasContentPropertiesObject(): bool
    {
        return isset($this->content);
    }

    /**
     * @param string $placeholder
     * @return false|int|string
     */
    protected function lookupRecordIdRoutePart(string $placeholder='')
    {
        $placeholders = ['#', '[#]'];
        if ($placeholder) {
            $placeholders[] = $placeholder;
        }

        // offset in request uri to first route part
        if (!isset($_SERVER) || !array_key_exists('REQUEST_URI', $_SERVER)) {
            return false;
        }
        $uri = $_SERVER['REQUEST_URI'];
        $uri_parts = explode('/', trim($uri, '/'));
        $offset = array_search(static::$route_parts[0], $uri_parts);

        foreach($placeholders as $ph) {
            $index = array_search($ph, static::$route_parts);
            if ($index !== false) {
                if (count($uri_parts) > $offset + $index) {
                    return $uri_parts[$offset + $index];
                }
                else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Retrieves content data from the database
     * @return APIRecordRoute
     * @throws ConfigurationUndefinedException
     */
    public function retrieveContentData(): APIRecordRoute
    {
        if (!$this->hasContentPropertiesObject()) {
            return $this;
        }
        if ($this->content->id->value === null || $this->content->id->value < 1) {
            throw new ConfigurationUndefinedException('A record id was not provided.');
        }
        call_user_func_array([$this::getControllerClass(), 'retrieveContentDataByType'], array($this->content));
        return $this;
    }

    /**
     * Loads the content object and uses the internal record id property value to hydrate the object's property value from the database.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     */
    public function retrieveContentObjectAndData(): APIRecordRoute
    {
        $ajax_data = static::getAjaxRequestData();
        return $this
            ->initializeContentObject(null, $ajax_data)
            ->collectContentID($ajax_data)
            ->retrieveContentData();
    }

    /**
     * Hydrates the content properties object by retrieving data from the database.
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException|InvalidValueException
     * @throws NotImplementedException
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
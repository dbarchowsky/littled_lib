<?php

namespace Littled\API;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Validation\Validation;

class APIListingsRoute extends APIRoute
{
    /**
     * @inheritDoc
     * @return $this
     * @throws ConfigurationUndefinedException|NotImplementedException|InvalidStateException
     */
    public function collectRequestData(?array $src = null): APIRoute
    {
        parent::collectRequestData($src);
        $content_type_id = Validation::collectIntegerRequestVar(LittledGlobals::CONTENT_TYPE_KEY, null, $src);
        if (!isset($this->filters)) {
            if ($content_type_id === null || $content_type_id < 1) {
                throw new ConfigurationUndefinedException('Content type not provided.');
            }
            $this->initializeFiltersObject($content_type_id);
        }
        $this->filters->collectFilterValues(true, [], $src);
        return $this;
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
    public function getContentTypeKey(): string
    {
        if (isset($this->filters)) {
            return $this->filters->content_properties->id->key;
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function hasContentPropertiesObject(): bool
    {
        return isset($this->filters);
    }

    /**
     * @inheritDoc
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws FailedQueryException
     * @throws InvalidValueException
     */
    protected function retrieveCoreContentProperties(): APIRoute
    {
        $this->filters->content_properties->read();
        return $this;
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException|InvalidStateException
     */
    public function setContentTypeId(int $content_id): APIRoute
    {
        if (!isset($filters)) {
            $this->initializeFiltersObject($content_id);
        }
        $this->filters->content_properties->id->setInputValue($content_id);
        return $this;
    }
}
<?php
namespace Littled\API;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordUnavailableException;
use Littled\PageContent\SiteSection\ContentProperties;


class APIListingsRoute extends APIRoute
{
    public function __construct()
    {
        static::setDefault('operation', APIRouteProperties::LISTINGS_TOKEN);
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @param array|null $src
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws NotImplementedException
     * @throws RecordUnavailableException
     */
    public function collectRequestData(?array $src = null): static
    {
        parent::collectRequestData($src);
        if (!isset($this->filters)) {
            $this->initializeFiltersObject();
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
}
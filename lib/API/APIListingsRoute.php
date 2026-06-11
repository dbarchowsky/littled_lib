<?php

namespace Littled\API;

use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordUnavailableException;
use Littled\PageContent\SiteSection\SectionContent;


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
     * @throws NotImplementedException
     * @throws RecordUnavailableException
     * @throws ContentValidationException
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

    public function setContent(SectionContent $content): static
    {
        // Placeholder for PageRouter class methods. No content property exists for record listings routes.
        return $this;
    }
}
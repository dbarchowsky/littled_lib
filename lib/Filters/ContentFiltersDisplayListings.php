<?php
namespace Littled\Filters;

use Littled\PageContent\SiteSection\ContentProperties;
use mysqli;

class ContentFiltersDisplayListings extends ContentFilters
{
    public function __construct(string $properties_class = ContentProperties::class, ?mysqli $mysqli = null)
    {
        parent::__construct($properties_class, $mysqli);
        $this->display_listings->value = true;
    }

    /**
     * @inheritDoc
     */
    protected function collectDisplayListingsSetting(?array $src=null): void
    {
        parent::collectDisplayListingsSetting($src);
        if ($this->display_listings->value===null) {
            $this->display_listings->value = true;
        }
    }
}
<?php

namespace Littled\Tests\Filters\Samples;

use Littled\Filters\SocialGalleryFilters;

class SocialGalleryFiltersChild extends SocialGalleryFilters
{
    /** @var int */
    protected static $content_type_id = 11; /* sketchbook on littledamien site */

    public function __construct()
    {
        parent::__construct();
        $this->page->value = 1;
    }
}
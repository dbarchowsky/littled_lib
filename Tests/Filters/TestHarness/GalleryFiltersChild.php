<?php

namespace Littled\Tests\Filters\Samples;

use Littled\Filters\GalleryFilters;

class GalleryFiltersChild extends GalleryFilters
{
    /** @var int */
    protected static ?int $content_type_id = 11; /* sketchbook on littledamien site */

    public function __construct()
    {
        parent::__construct();
        $this->page->value = 1;
    }
}
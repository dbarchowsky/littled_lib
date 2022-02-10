<?php

namespace Littled\PageContent\SiteSection\TestHarness;

use Littled\PageContent\SiteSection\SectionContent;

/**
 * Implements abstract methods of SectionContent to allow objects in unit tests.
 *
 */
class SectionContentTestHarness extends SectionContent
{
    public function generateUpdateQuery(): ?array
    {
       return array();
    }
}
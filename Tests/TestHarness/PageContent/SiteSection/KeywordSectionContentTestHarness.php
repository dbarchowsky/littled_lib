<?php
namespace Littled\Tests\TestHarness\PageContent\SiteSection;

use Littled\PageContent\SiteSection\KeywordSectionContent;

class KeywordSectionContentTestHarness extends KeywordSectionContent
{
	protected static int $content_type_id = 6037;

    /**
     * Implements abstract method in order to be able to test the parent class.
     */
    public function generateUpdateQuery(): ?array
    {
        return array();
    }
}
<?php
namespace Littled\Tests\TestHarness\Request;

use Littled\Request\CategorySelect;
use Littled\Tests\PageContent\SiteSection\SectionContentTest;


class CategorySelectTestHarness extends CategorySelect
{
    protected static int    $content_type_id = 6168;  // << test_table categories, e.g. "Test Category" in site_section table
    protected int           $parent_id = SectionContentTest::TEST_RECORD_ID;

	/**
	 * Public interface for tests.
	 * @inheritDoc
	 */
	public function pushKeywordInstance(string $term)
	{
		parent::pushKeywordInstance($term);
	}
}
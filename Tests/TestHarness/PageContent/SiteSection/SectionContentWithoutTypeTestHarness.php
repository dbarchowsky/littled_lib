<?php
namespace LittledTests\TestHarness\PageContent\SiteSection;

use Littled\PageContent\SiteSection\SectionContent;


class SectionContentWithoutTypeTestHarness extends SectionContent
{
	public const                ID_KEY = 'testId';
	/** Purposefully leave the $content_type_id property undefined for testing purposes. */
	// protected static int        $content_type_id = 6037;
	protected static string     $table_name='test_table';
}
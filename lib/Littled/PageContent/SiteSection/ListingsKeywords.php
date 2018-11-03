<?php
namespace Littled\PageContent\SiteSection;


use Littled\Request\IntegerInput;

/**
 * Class ListingsKeywords
 * @package Littled\PageContent\SiteSection
 * Extends KeywordSectionContent by providing a "record id" property to be used to retrieve keywords attached
 * to a particular record in the database. Intended to be used as a helper class for retrieving keywords while
 * rendering listings.
 */
class ListingsKeywords extends KeywordSectionContent
{
	/** @var IntegerInput Id of the current record being displayed. */
	public $id;

	/**
	 * ListingsKeywords constructor.
	 * @param int $content_type_id This article's content type identifier.
	 * @param string[optional] $kw_param Keyword request variable name.
	 */
	public function __construct($content_type_id, $kw_param = "kw")
	{
		parent::__construct($content_type_id, $kw_param);
		$this->id = new IntegerInput("Record id", "id");
	}
}
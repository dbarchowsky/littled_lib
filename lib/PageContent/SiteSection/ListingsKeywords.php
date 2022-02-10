<?php
namespace Littled\PageContent\SiteSection;


use Littled\App\LittledGlobals;
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
	 * @param int $id The article's id.
	 * @param int $content_content_id This article's content type identifier.
	 * @param string[optional] $kw_param Keyword request variable name.
	 */
	public function __construct($id, $content_content_id, $keyword_param = "kw")
	{
		parent::__construct($id, $content_content_id, $keyword_param);
		$this->id = new IntegerInput("Record id", LittledGlobals::ID_KEY, false, $id);
	}
}
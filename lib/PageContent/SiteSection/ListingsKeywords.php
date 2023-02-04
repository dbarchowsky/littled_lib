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
	/** @var IntegerInput The id of the current record being displayed. */
	public IntegerInput $id;

	/**
	 * ListingsKeywords constructor.
	 * @param ?int $id The article's id.
	 * @param int $content_type_id This article's content type identifier.
	 * @param string $keyword_key Optional key for the term's request variable.
	 */
	public function __construct(?int $id, int $content_type_id, string $keyword_key = "kw")
	{
		parent::__construct($id, $content_type_id, $keyword_key);
		$this->id = new IntegerInput("Record id", LittledGlobals::ID_KEY, false, $id);
	}
}
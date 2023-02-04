<?php
namespace Littled\PageContent\SiteSection;

use Littled\App\LittledGlobals;
use Littled\Request\IntegerInput;


/**
 * Class ListingsKeywords
 * @property IntegerInput $id The ide of the current record being displayed.
 * Extends KeywordSectionContent by providing a "record id" property to be used to retrieve keywords attached
 * to a particular record in the database. Intended to be used as a helper class for retrieving keywords while
 * rendering listings.
 */
class ListingsKeywords extends KeywordSectionContent
{
	public IntegerInput $id;

	/**
	 * @inheritDoc
	 */
	public function __construct(?int $id, int $content_type_id)
	{
		parent::__construct($id, $content_type_id);
		$this->id = new IntegerInput("Record id", LittledGlobals::ID_KEY, false, $id);
	}
}
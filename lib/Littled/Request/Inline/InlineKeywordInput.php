<?php
namespace Littled\Request\Inline;


use Littled\Cache\ContentCache;
use Littled\Keyword\Keyword;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use Littled\Request\IntegerInput;

/**
 * Class InlineKeywordInput
 * @package Littled\Request\Inline
 */
class InlineKeywordInput extends KeywordSectionContent
{
	public $id;

	/**
	 * InlineKeywordInput constructor.
	 * @param int|null[optional] $id Main record id.
	 * @param int|null[optional] $content_type_id Content type identifier.
	 * @param string[optional] $keyword_param Name of the request variable that passes in keyword content.
	 */
	function __construct($id = null, $content_type_id = null, string $keyword_param = 'kw')
	{
		parent::__construct($id, $content_type_id, $keyword_param);
		$this->id = new IntegerInput("Record ID", Keyword::PARENT_PARAM, true, null);
		$this->contentProperties->id->key = Keyword::TYPE_PARAM;
	}

	/**
	 * @param null $src
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function collectFromInput($src = null)
	{
		parent::collectFromInput($src);
		$this->retrieveSectionProperties();
	}

	public function saveKeywords()
	{
		parent::saveKeywords();
		ContentCache::updateKeywords($this->id->value, $this->contentProperties->id->value);
	}
}
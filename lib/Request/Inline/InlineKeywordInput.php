<?php
namespace Littled\Request\Inline;


use Littled\Cache\ContentCache;
use Littled\Keyword\Keyword;
use Littled\PageContent\PageContent;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use Littled\Request\IntegerInput;

/**
 * Class InlineKeywordInput
 * @package Littled\Request\Inline
 */
class InlineKeywordInput extends KeywordSectionContent
{
	/** @var IntegerInput ID of the content linked to the keywords. */
	public $id;

	/**
	 * InlineKeywordInput constructor.
	 * @param int|null[optional] $id Main record id.
	 * @param int|null[optional] $content_type_id Content type identifier.
	 * @param string[optional] $keyword_param Name of the request variable that passes in keyword content.
	 */
	function __construct($id = null, $content_type_id = null, $keyword_param = 'kw')
	{
		parent::__construct($id, $content_type_id, $keyword_param);
		$this->id = new IntegerInput("Record ID", Keyword::PARENT_PARAM, true, null);
		$this->contentProperties->id->key = Keyword::TYPE_PARAM;
	}

	/**
	 * Fill keyword properties from form data.
	 * @param array[optional] $src Optional array containing data to use in place of POST data.
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

	/**
	 * @return string Keyword list markup.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function loadKeywordListMarkup()
	{
		return PageContent::loadTemplateContent($this::getKeywordsListTemplatePath(),
			array('keywords' => $this->formatKeywordList()));
	}

	/**
	 * Commits keyword data to the database
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function saveKeywords()
	{
		parent::saveKeywords();
		ContentCache::updateKeywords($this->id->value, $this->contentProperties->id->value);
	}
}
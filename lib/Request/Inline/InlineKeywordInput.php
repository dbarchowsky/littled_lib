<?php
namespace Littled\Request\Inline;

// use Littled\Cache\ContentCache;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\ContentUtils;
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
	function __construct($id = null, $content_type_id = null, $keyword_key = 'kw')
	{
		parent::__construct($id, $content_type_id, $keyword_key);
		$this->id = new IntegerInput("Record ID", Keyword::PARENT_KEY, true, null);
		$this->content_properties->id->key = Keyword::TYPE_KEY;
	}

	/**
	 * Fill keyword properties from form data.
	 * @param ?array $src Optional array containing data to use in place of POST data.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function collectRequestData(?array $src = null)
	{
		parent::collectRequestData($src);
		$this->retrieveSectionProperties();
	}

	/**
	 * @return string Keyword list markup.
	 * @throws ResourceNotFoundException
	 */
	public function loadKeywordListMarkup(): string
	{
		return ContentUtils::loadTemplateContent($this::getKeywordsListTemplatePath(),
			array('content' => &$this));
	}

	/**
	 * Commits keyword data to the database
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 */
	public function saveKeywords()
	{
		parent::saveKeywords();
		// ContentCache::updateKeywords($this->id->value, $this->contentProperties->id->value);
	}
}
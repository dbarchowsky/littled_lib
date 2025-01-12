<?php
namespace Littled\Request\Inline;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use Littled\Request\PrimaryKeyInput;


class InlineKeywordInput extends KeywordSectionContent
{
    /**
     * InlineKeywordInput constructor.
     * @param int|null $id Main record id.
     * @param int|null $content_type_id Content type identifier.
     * @throws InvalidStateException
     */
    function __construct(int|null $id = null, int|null $content_type_id = null)
    {
        parent::__construct($id, $content_type_id);
        $this->id = new PrimaryKeyInput('Record ID', Keyword::PARENT_KEY, true, null);
        $this->content_properties->id->key = Keyword::TYPE_KEY;
    }

    /**
     * Fill keyword properties from form data.
     * @param array|null $src Optional array containing data to use in place of POST data.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws InvalidStateException
     * @throws RecordNotFoundException
     */
    public function collectRequestData(?array $src = null): void
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
            ['content' => &$this]);
    }
}
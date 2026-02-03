<?php
namespace Littled\Request\Inline;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\KeywordSectionContent;


class InlineKeywordInput extends KeywordSectionContent
{
    /**
     * InlineKeywordInput constructor.
     * @param int|null $id Main record id.
     * @param int|null $content_type_id Content type identifier.
     */
    function __construct(int|null $id = null, int|null $content_type_id = null)
    {
        parent::__construct($id, $content_type_id);
        $this->content_properties = (new ContentProperties())
            ->shareConnection($this)
            ->setLabel('Content type')
            ->setAsRequired();
        $this->id
            ->setLabel('Record id')
            ->setKey(Keyword::TYPE_KEY)
            ->setAsRequired();
    }

    /**
     * Fill keyword properties from form data.
     * @param array|null $src Optional array containing data to use in place of POST data.
     * @return $this
     * @throws ConfigurationUndefinedException
     */
    public function collectRequestData(?array $src = null): static
    {
        parent::collectRequestData($src);
        // $this->retrieveSectionProperties();
        return $this;
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
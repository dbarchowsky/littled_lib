<?php
namespace Littled\PageContent\SiteSection;

use Exception;
use Littled\Request\CategorySelect;


/**
 * Extends SectionContent by adding keyword properties to standardize retrieving and committing keyword terms associated with a content record.
 */
abstract class CategorySectionContent extends KeywordSectionContent
{
    public static string        $category_class = CategorySelect::class;
    /** @var CategorySelect     Category keywords. */
    public CategorySelect       $categories;

    public function __construct($id = null, $content_type_id = null)
    {
        parent::__construct($id, $content_type_id);
        $this->categories = new static::$category_class();
    }

    /**
     * Overrides parent routine to additionally delete category keywords.
     * @return string
     * @throws Exception
     */
    public function deleteKeywords( ): string
    {
        parent::deleteKeywords();
        $this->categories->deleteRecords();
        return 'Keywords and categories were successfully deleted. ';
    }

    /**
     * Overrides parent routine to retrieve both category keywords and free-form keywords.
     * @throws Exception
     */
    public function readKeywords ()
    {
        $this->categories->setParentId($this->id->value);
        parent::readKeywords();
        $this->categories->read();
    }

    /**
     * @inheritDoc
     */
    public function saveKeywords ( )
    {
        $this->categories->setParentId($this->id->value);
        parent::saveKeywords();
        $this->categories->save();
        $this->updateKeywordCache();
    }

    /**
     * Updates a concatenated list of keywords attached to this record, that is stored in the database.
     * @return void
     * @throws Exception
     */
    abstract protected function updateKeywordCache(): void;
}
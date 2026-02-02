<?php
namespace Littled\PageContent\SiteSection;

use Littled\Exception\CommitException;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\ReadException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\RecordUnavailableException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\ContentUtils;
use Littled\Request\StringTextarea;


/**
 * Extends SectionContent by adding keyword properties to standardize retrieving and committing keyword terms
 * associated with a content record.
 */
class KeywordSectionContent extends SectionContent
{
    /** @var StringTextarea     Container for collecting keyword form data. */
    public StringTextarea       $keyword_input;
    /** @var Keyword[]          List of all keywords linked to the parent record. */
    public array                $keywords = [];
    /** @var string             Path to template used to display individual keyword terms on frontend pages */
    protected static string     $keyword_cell_template = '';
    /** @var string             Path to template used on frontend pages to display all keyword terms linked to the
     *                          parent record.
     */
    protected static string     $keyword_list_template = '';
    protected static string     $keyword_key = 'kw';
    /** @var int                Keyword category record id */
    protected static int        $keyword_category_id;

    /**
     * KeywordSectionContent constructor.
     * @param ?int $id ID Optional value representing this object's record in the database. Defaults to NULL.
     * @param ?int $content_type_id Optional ID of this object's content type. Defaults to NULL.
     */
    function __construct(int|null $id = null, int|null $content_type_id = null)
    {
        parent::__construct($id, $content_type_id ?: $this->getContentTypeId());

        $this->content_properties->id->key = static::$keyword_key . $this->content_properties->id->key;

        /* Suppress generalized error messages related to the content type properties */
        $this->content_properties->validation_message = '';

        $this->keyword_input = new StringTextarea('Keywords', static::$keyword_key . 'Text', false, '', 1000, null);

        $this->keyword_input->is_database_field = false;
        $this->validation_message = 'Problems found in keywords.';
    }

    /**
     * Pushes a new keyword term onto the current list of Keyword objects stored in the object's $keyword property.
     * @return $this
     * @param string $term Keyword term to push onto the stack.
     * @param bool $test_for_parent Optional flag to bypass testing for a valid parent id when adding the keyword.
     * @throws ConfigurationUndefinedException
     */
    public function addKeyword(string $term, bool $test_for_parent = true): KeywordSectionContent
    {
        if ($test_for_parent) {
            $this->testForParentID('Could not add keyword.');
        }
        $this->testForContentType('Could not add keyword.');
        if (null === $this->content_properties->id->value) {
            throw new ConfigurationUndefinedException('Could not add keyword. Content type not set.');
        }
        $kw = new Keyword($term, $this->id->value, $this->content_properties->id->value);
        if (!$test_for_parent) {
            $kw->parent_id->required = false;
        }
        $this->keywords[] = $kw;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    public function base64DecodeInput(): void
    {
        parent::base64DecodeInput();
        $this->collectKeywordInput();
    }

    /**
     * Clears and resets internal keyword values.
     */
    public function clearKeywordData(): void
    {
        $this->clearKeywordList();
        $this->keyword_input->value = '';
    }

    /**
     * Removes all keywords from the current keyword list while preserving any form data.
     */
    public function clearKeywordList(): void
    {
        $this->keywords = array();
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    public function collectRequestData(?array $src = null): static
    {
        parent::collectRequestData($src);
        $this->content_properties->id->collectRequestData($src);
        $this->collectKeywordInput();
        return $this;
    }

    /**
     * Sets object values that need to be set after submitting data from an inline form, typically a widget within a
     * page that uses AJAX to edit keyword values.
     * @param ?array $src Optional array container of request variables.
     * If specified, it will override inspecting the $_POST and $_GET collections for keyword values.
     */
    public function collectFromInlineInput(?array $src = null): void
    {
        $this->id->collectRequestData($src);
    }

    /**
     * Collects keyword terms from http request and stores them as separate Keyword objects in the object's $keywords
     * property.
     * @param null|array $src Optional array container of request variables. If specified, it will override inspecting the
     * $_POST and $_GET collections for keyword values.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    public function collectKeywordInput(?array $src = null): void
    {
        $this->clearKeywordData();
        $this->keyword_input->collectRequestData($src);
        if (!$this->keyword_input->value) {
            return;
        }
        $keywords = $this->extractKeywordTerms($this->keyword_input->value);
        foreach ($keywords as $term) {
            $this->addKeyword(trim($term), false);
        }
    }

    /**
     * @inheritDoc
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws InvalidStateException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     * @throws RecordUnavailableException
     */
    public function delete(): string
    {
        $status = parent::delete();
        $status .= $this->deleteKeywords();
        return ($status);
    }

    /**
     * Deletes any keyword records linked to the main content record represented by the object.
     * @return string String containing a description of the results of the deletion.
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function deleteKeywords(): string
    {
        $this->testForParentID();
        $this->testForContentType();

        $query = 'CALL keywordDeleteLinked (?,?)';
        $this->query($query, 'ii', $this->id->value, $this->content_properties->id->value);
        return ('All linked keyword records were deleted.');
    }

    /**
     * Extract keyword terms from a comma-delimited string containing multiple terms.
     * @param array|string $src Comma-delimited series of keyword terms.
     * @return array Keyword terms separated out into an array.
     */
    protected function extractKeywordTerms(array|string $src): array
    {
        if (is_array($src)) {
            $terms = array_values($src);
        } else {
            $terms = array_unique(explode(',', $src));
        }
        $terms = array_filter($terms, function ($term) {
            return (strlen($term) > 0);
        });
        array_walk($terms, function (&$term) {
            $term = trim(htmlentities(strip_tags($term)));
        });
        return ($terms);
    }

    /**
     * Retrieves the keyword template path from the database and uses the value to set the class's keyword template path property.
     */
    protected function fetchKeywordListTemplate(): void
    {
        $t = $this->content_properties->getContentTemplateByName('keyword_list');
        if ($t) {
            self::setKeywordsListTemplatePath($t->path->value);
        }
    }

    /**
     * Formats a comma-delimited string out of the object's keywords.
     * @param bool $fetch_from_database Optional. If TRUE return keywords from the database. If FALSE return keyword terms.
     * The default value is TRUE.
     * @return string Comma-delimited string containing all the current keywords associated with this record.
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function formatKeywordList(bool $fetch_from_database = true): string
    {
        if ($fetch_from_database && $this->hasData()) {
            $this->readKeywords();
        }
        return (stripslashes(join(', ', array_map([self::class, 'termCallback'], $this->keywords))));
    }

    /**
     * Returns markup containing keywords as links to listings filtered by the keyword value.
     * @param array $context (Optional) Array containing variables to insert into the template.
     * @return string Markup to be used to display the keywords. False on error retrieving markup content.
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws ResourceNotFoundException
     */
    public function formatKeywordListPageContent(array $context = array()): string
    {
        if ($this->hasData()) {
            $this->readKeywords();
        }
        $context['content'] = &$this;
        if (!self::getKeywordsListTemplatePath()) {
            $this->fetchKeywordListTemplate();
        }
        return (ContentUtils::loadTemplateContent(self::getKeywordsListTemplatePath(), $context));
    }

    /**
     * Keyword category id getter.
     * @return int The id of the keyword category.
     * @throws ConfigurationUndefinedException
     */
    public static function getKeywordCategoryId(): int
    {
        if (!isset(static::$keyword_category_id)) {
            throw new ConfigurationUndefinedException('Keyword category not specified.');
        }
        return static::$keyword_category_id;
    }

    /**
     * Keyword key getter.
     * @return string
     */
    public static function getKeywordKey(): string
    {
        return static::$keyword_key;
    }

    /**
     * Returns a path to the keyword container template.
     * @return string Path to keywords container template.
     */
    public static function getKeywordsCellTemplatePath(): string
    {
        return (static::$keyword_cell_template);
    }

    /**
     * Returns a path to the keyword list template
     * @return string Path to the keyword list template.
     */
    public static function getKeywordsListTemplatePath(): string
    {
        return (static::$keyword_list_template);
    }

    /**
     * Returns an array containing just the keyword terms as strings for each keyword linked to the record in the database.
     * @param bool $fetch_from_database Optional. If TRUE return keywords from the database. If FALSE return keyword terms.
     * The default value is TRUE.
     * @return array List of keyword terms currently linked to the record in the database.
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function getKeywordTermsArray(bool $fetch_from_database = true): array
    {
        if ($fetch_from_database && $this->hasData()) {
            $this->readKeywords();
        }
        return (array_map([KeywordSectionContent::class, 'termCallback'], $this->keywords));
    }

    /**
     * Returns flag indicating whether keywords have been loaded into the object.
     * @return bool Returns TRUE if keywords have been loaded into the object. FALSE otherwise.
     */
    public function hasKeywordData(): bool
    {
        if (strlen('' . $this->keyword_input->value) > 0) {
            return true;
        }
        foreach ($this->keywords as $keyword) {
            if (strlen($keyword->term->value) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->hasKeywordData();
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     * @throws ReadException
     */
    public function read(): static
    {
        parent::read();
        $this->content_properties->read();
        $this->readKeywords();
        return $this;
    }

    /**
     * Retrieves keywords linked to the current record.
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function readKeywords(): void
    {
        $this->testForParentID();
        $this->testForContentType();

        $this->clearKeywordList();
        $query = 'CALL keywordSelectLinked(?,?)';
        $data = $this->fetchRecords($query, 'ii', $this->id->value, $this->content_properties->id->value);

        foreach ($data as $row) {
            $i = count($this->keywords);
            $this->keywords[$i] = new Keyword($row->term, $this->id->value, $this->content_properties->id->value, $row->count);
        }
        $this->keyword_input->value = implode(', ', $this->getKeywordTermsArray(false));
    }

    /**
     * Commits object property data to record in the database.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws ReadException
     * @throws RecordNotFoundException
     * @throws CommitException
     */
    public function save(): void
    {
        parent::save();
        $this->saveKeywords();
        // ContentCache::updateKeywords($this->id->value, $this->contentProperties);
    }

    /**
     * Saves all keywords linked to the main record object.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function saveKeywords(): static
    {
        $this->testForParentID('Could not serialize keywords.');
        $this->deleteKeywords();
        foreach ($this->keywords as $keyword) {
            $keyword->parent_id->value = $this->id->value;
            $keyword->save();
        }
        return $this;
    }

    /**
     * Sets the content type record id property value.
     * @param int $id
     * @return $this
     */
    public function setContentType(int $id): static
    {
        $this->content_properties->setRecordId($id);
        return $this;
    }

    /**
     * Keyword category id setter.
     * @param int $id
     * @return void
     */
    public static function setKeywordCategoryId(int $id): void
    {
        static::$keyword_category_id = $id;
    }

    /**
     * Keyword key setter.
     * @param string $key
     * @return void
     */
    public static function setKeywordKey(string $key): void
    {
        static::$keyword_key = $key;
    }

    /**
     * Sets the class's keyword cell template path property.
     * @param string $path Path to the template file.
     */
    public static function setKeywordsCellTemplatePath(string $path): void
    {
        static::$keyword_cell_template = $path;
    }

    /**
     * Sets the class's keyword list template path property.
     * @param string $path Path to the template file.
     */
    public static function setKeywordsListTemplatePath(string $path): void
    {
        static::$keyword_list_template = $path;
    }

    /**
     * Set record id property value.
     * @param int|null $record_id
     * @return $this
     */
    public function setRecordId(?int $record_id): static
    {
        parent::setRecordId($record_id);
        return $this;
    }

    /**
     * Returns the term value of the Keyword object.
     * @param Keyword $keyword Keyword object containing a keyword term.
     * @return string Keyword term.
     */
    protected static function termCallback(Keyword $keyword): string
    {
        return ($keyword->term->value);
    }

    /**
     * Validates the internal property values of the object for data that is not valid.
     * Updates the $validation_errors property of the object with messages describing the invalid values.
     * @param array $exclude_properties
     * @param bool $clear_existing
     * @throws ContentValidationException Errors found in the form data.
     */
    public function validateInput(array $exclude_properties = [], bool $clear_existing = true): void
    {
        try {
            /* bypass validation of site section properties */
            $exclude_properties[] = 'content_properties';
            parent::validateInput($exclude_properties, $clear_existing);
        } catch (ContentValidationException) {
            /* continue validating collected request data */
        }
        try {
            /* validate the content type id to ensure this record has a content type value */
            $this->content_properties->id->validate();
        } catch (ContentValidationException $ex) {
            $this->addValidationError($ex->getMessage());
        }
        foreach ($this->keywords as $keyword) {
            try {
                $keyword->validateInput();
            } catch (ContentValidationException) {
                $this->addValidationError($keyword->validationErrors());
            }
        }
        if ($this->hasValidationErrors()) {
            throw new ContentValidationException($this->validation_message);
        }
    }
}
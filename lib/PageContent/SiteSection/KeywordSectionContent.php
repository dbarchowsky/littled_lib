<?php
namespace Littled\PageContent\SiteSection;


// use Littled\Cache\ContentCache;
use Exception;
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
use Littled\Request\StringTextarea;
use Littled\Ajax\ContentAjaxProperties;

abstract class KeywordSectionContent extends SectionContent
{
	/** @var StringTextarea Container for keyword form data. */
	public StringTextarea $keywordInput;
	/** @var Keyword[] Array of Keyword objects. */
	public array $keywords=[];
	/** @var string Path to template that renders keyword cells that combine a keyword list with buttons to edit the keywords. */
	protected static string $keywordCellTemplate = '';
	/** @var string Path to keyword list template file. */
	protected static string $keywordListTemplate = '';

	/**
	 * KeywordSectionContent constructor.
	 * @param int|null[optional] $id ID value representing this object's record in the database. Defaults to NULL.
	 * @param int|null[optional] $content_type_id ID of this object's content type. Defaults to NULL.
	 * @param string[optional] $keyword_param Name of the variable containing comma-delimited keywords collected from HTML forms.
	 */
	function __construct($id = null, $content_type_id = null, $keyword_param='kw')
	{
		parent::__construct($id, $content_type_id);

		if ($keyword_param != 'kw') {
			$this->content_properties->id->key = $keyword_param.$this->content_properties->id->key;
		}
		/* Suppress generalized error messages related to the content type properties */
		$this->content_properties->validationMessage = '';

		$this->keywordInput = new StringTextarea("Keywords", "{$keyword_param}te", false, '', 1000, null);

		$this->keywordInput->is_database_field = false;
	}

	/**
	 * Pushes a new keyword term onto the current list of Keyword objects stored in the object's $keyword property.
	 * @param string $term Keyword term to push onto the stack.
     * @throws Exception
	 */
	public function addKeyword( string $term ): void
	{
        $this->testForParentID('Could not add keyword.');
        $this->testForContentType('Could not add keyword.');
        if (null === $this->content_properties->id->value) {
            throw new Exception('Could not add keyword. Content type not set.');
        }
		$this->keywords[] = new Keyword($term, $this->id->value, $this->content_properties->id->value);
	}

	/**
	 * Clears and resets internal keyword values.
	 */
	public function clearKeywordData(): void
	{
		$this->clearKeywordList();
		$this->keywordInput->value = "";
	}

	/**
	 * Removes all keywords from the current keyword list while preserving any form data.
	 */
	public function clearKeywordList(): void
	{
		$this->keywords = array();
	}

	/**
	 * Fills object property values using data collected from request variables.
	 * @param ?array $src Optional array container of request variables. If specified, it will override
	 * inspecting the $_POST and $_GET collections for keyword values.
	 */
	public function collectRequestData(?array $src = null)
	{
		parent::collectRequestData($src);
		$this->content_properties->id->collectRequestData($src);
		$this->collectKeywordInput();
	}

	/**
	 * Sets object values that need to be set after submitting data from an inline form, typically a widget within a
	 * page that uses AJAX to edit keyword values.
	 * @param ?array $src Optional array container of request variables. If specified, it will override
	 * inspecting the $_POST and $_GET collections for keyword values.
	 */
	public function collectFromInlineInput(?array $src=null): void
	{
		$this->id->collectRequestData($src);
	}

	/**
	 * Collects keyword terms from http request and stores them as separate Keyword objects in the object's
	 * $keywords property.
	 * @param ?array $src Optional array container of request variables. If specified, it will override
	 * inspecting the $_POST and $_GET collections for keyword values.
	 */
	public function collectKeywordInput(?array $src=null): void
	{
		$this->clearKeywordData();
		$this->keywordInput->collectRequestData($src);
		if (!$this->keywordInput->value) {
			return;
		}
		$keywords = $this->extractKeywordTerms($this->keywordInput->value);
		foreach($keywords as $term) {
			$this->keywords[] = new Keyword(trim($term), $this->id->value, $this->content_properties->id->value);
		}
	}

	/**
	 * Removes the record from the database that corresponds to the object's id value. Also removes any records linked
	 * to that main record.
	 * @return string String containing a description of the results of the deletion.
	 * @throws ContentValidationException
     * @throws NotImplementedException
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
     * @throws ContentValidationException
     * @throws Exception
     */
	public function deleteKeywords(): string
	{
		$this->testForParentID();
		$this->testForContentType();

		$query = "CALL keywordDeleteLinked (?,?)";
		$this->query($query, 'ii', $this->id->value, $this->content_properties->id->value);
		return ("All linked keyword records were deleted.");
	}

	/**
	 * Extract keyword terms from a comma-delimited string containing multiple terms.
	 * @param string|array $src Comma-delimited series of keyword terms.
	 * @return array Keyword terms separated out into an array.
	 */
	protected function extractKeywordTerms($src): array
    {
		if (is_array($src)) {
			$terms = array_values($src);
		}
		else {
			$terms = array_unique(explode(',', $src));
		}
		$terms = array_filter($terms, function($term) { return(strlen($term) > 0);});
		array_walk($terms, function(&$term) {
			$term = trim(htmlentities(strip_tags($term)));
		});
		return ($terms);
	}

	/**
	 * Retrieves the keyword template path from the database and uses the value to set the class's keyword template path property.
     * @throws RecordNotFoundException
	 */
	protected function fetchKeywordListTemplate(): void
	{
		$ao = new ContentAjaxProperties($this->content_properties->id->value);
		$ao->retrieveContentProperties();
		self::setKeywordsListTemplatePath($ao->keywords_template->value);
	}

	/**
	 * Formats a comma-delimited string out of the object's keywords.
	 * @param bool[optional] $fetch_from_database If TRUE return keywords from database. If FALSE return keyword terms
	 * stored in the object properties. Defaults to TRUE.
	 * @return string Comma-delimited string containing all the current keywords associated with this record.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     */
	public function formatKeywordList($fetch_from_database=true): string
	{
		if ($fetch_from_database && $this->hasData()) {
			$this->readKeywords();
		}
		return(stripslashes(join(', ', array_map('self::termCallback', $this->keywords))));
	}

	/**
	 * Returns markup containing keywords as links to listings filtered by the keyword value.
	 * @param array $context (Optional) Array containing variables to insert into the template.
	 * @return string|false Markup to be used to display the keywords. False on error retrieving markup content.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     * @throws RecordNotFoundException
	 * @throws ResourceNotFoundException
	 */
	public function formatKeywordListPageContent(array $context=array()): string
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
     * Implement abstract method not referenced for unit test purposes.
     */
    public function generateUpdateQuery(): ?array
    {
        return array();
    }

    /**
     * Returns path to keywords container template.
     * @return string Path to keywords container template.
     */
	public static function getKeywordsCellTemplatePath(): string
    {
        return (static::$keywordCellTemplate);
    }

    /**
     * Returns path to keywords list template
     * @return string Path to keywords list template.
     */
    public static function getKeywordsListTemplatePath(): string
    {
        return (static::$keywordListTemplate);
    }

	/**
	 * Returns an array containing just the keyword terms as strings for each keyword linked to the record in the database.
	 * @param bool[optional] $fetch_from_database If TRUE return keywords from database. If FALSE return keyword terms
	 * stored in the object properties. Defaults to TRUE.
	 * @return array List of keyword terms currently linked to the record in the database.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     */
	public function getKeywordTermsArray( $fetch_from_database=true ): array
	{
		if ($fetch_from_database && $this->hasData()) {
			$this->readKeywords();
		}
		return(array_map('self::termCallback', $this->keywords));
	}

	/**
	 * Returns flag indicating whether keywords have been loaded into the object.
	 * @return bool Returns TRUE if keywords have been loaded into the object. FALSE otherwise.
	 */
	public function hasKeywordData(): bool
	{
		if (strlen(''.$this->keywordInput->value) > 0) {
			return true;
		}
        foreach($this->keywords as $keyword) {
            if (strlen($keyword->term->value) > 0) {
                return true;
            }
        }
		return false;
	}

	/**
	 * Retrieve record data.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
	public function read()
	{
		parent::read();
		$this->content_properties->read();
		$this->readKeywords();
	}

    /**
     * Retrieves keywords linked to the current record.
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws Exception
     */
	public function readKeywords()
	{
		$this->testForParentID();
		$this->testForContentType();

		$this->clearKeywordList();
		$this->connectToDatabase();
		$query = "CALL keywordSelectLinked(?,?)";
		$data = $this->fetchRecords($query, 'ii', $this->id->value, $this->content_properties->id->value);

		foreach($data as $row) {
			$i = count($this->keywords);
			$this->keywords[$i] = new Keyword($row->term, $this->id->value, $this->content_properties->id->value, $row->count);
		}
	}

	/**
	 * Commits object property data to record in the database.
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function save()
	{
		parent::save();
		$this->saveKeywords();
		// ContentCache::updateKeywords($this->id->value, $this->contentProperties);
	}

    /**
     * Saves all keywords linked to the main record object.
     * @throws ContentValidationException
     * @throws Exception
     */
	public function saveKeywords()
	{
		$this->deleteKeywords();
		foreach($this->keywords as $keyword) {
            $keyword->parent_id->value = $this->id->value;
			$keyword->save();
		}
	}

	/**
	 * Sets the class's keyword cell template path property.
	 * @param string $path Path to template file.
	 */
	public static function setKeywordsCellTemplatePath( string $path ): void
	{
		static::$keywordCellTemplate = $path;
	}

	/**
	 * Sets the class's keyword list template path property.
	 * @param string $path Path to template file.
	 */
	public static function setKeywordsListTemplatePath( string $path ): void
	{
		static::$keywordListTemplate = $path;
	}

	/**
	 * Returns the term value of the Keyword object.
	 * @param Keyword $keyword Keyword object containing a keyword term.
	 * @return string Keyword term.
	 */
	protected static function termCallback( Keyword $keyword ): string
	{
		return($keyword->term->value);
	}

	/**
	 * Validates the internal property values of the object for data that is not valid.
	 * Updates the $validation_errors property of the object with messages describing the invalid values.
	 * @param array $exclude_properties (Optional) Collection of variable names to ignore in the request data.
	 * @throws ContentValidationException Errors found in the form data.
	 */
	public function validateInput(array $exclude_properties=[])
	{
		try {
		    /* bypass validation of site section properties */
		    $exclude_properties[] = 'contentProperties';
			parent::validateInput($exclude_properties);
		}
		catch (ContentValidationException $ex) {
			/* continue validating collected request data */
		}
		try {
		    /* validate the content type id to ensure this record has a content type value */
		    $this->content_properties->id->validate();
        }
        catch(ContentValidationException $ex) {
            $this->validationErrors[] = $ex->getMessage();
        }
        foreach($this->keywords as $keyword) {
            try {
                $keyword->validateInput();
            }
            catch(ContentValidationException $ex) {
                $this->validationErrors = array_merge($this->validationErrors, $keyword->validationErrors);
            }
        }
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException($this->validationMessage);
		}
	}
}
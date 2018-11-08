<?php
namespace Littled\PageContent\SiteSection;


use Littled\Cache\ContentCache;
use Littled\Exception\ContentValidationException;
use Littled\Keyword\Keyword;
use Littled\PageContent\PageContent;
use Littled\Request\StringTextarea;

class KeywordSectionContent extends SectionContent
{
	/** @var StringTextarea Container for keyword form data. */
	public $keywordInput;
	/** @var array Array of Keyword objects. */
	public $keywords;
	/** @var string Path to keyword list template file. */
	public static $keywordListTemplate = '';

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
			$this->siteSection->id->key = $keyword_param.$this->siteSection->id->key;
		}
		/* Suppress generalized error messages related to the content type properties */
		$this->siteSection->validationMessage = '';

		$this->keywordInput = new StringTextarea("Keywords", "{$keyword_param}te", false, '', 1000, null);
		$this->keywords = array();

		$this->keywordInput->isDatabaseField = false;
	}

	/**
	 * Pushes a new keyword term onto the current list of Keyword objects stored in the object's $keyword property.
	 * @param string $term Keyword term to push onto the stack.
	 */
	public function addKeyword( $term )
	{
		array_push($this->keywords, new Keyword($term, $this->id->value, $this->siteSection->id->value));
	}

	/**
	 * Clears and resets internal keyword values.
	 */
	public function clearKeywordData()
	{
		$this->keywords = array();
		$this->keywordInput->value = "";
	}

	/**
	 * Fills object property values using data collected from request variables.
	 * @param array|null[optional] $src Optional array container of request variables. If specified, it will override
	 * inspecting the $_POST and $_GET collections for keyword values.
	 */
	public function collectFromInput($src = null)
	{
		parent::collectFromInput($src);
		$this->siteSection->id->collectFromInput(null, $src);
		$this->collectKeywordInput();
	}

	/**
	 * Sets object values that need to be set after submitting data from an inline form, typically a widget within a
	 * page that uses AJAX to edit keyword values.
	 * @param array|null[optional] $src Optional array container of request variables. If specified, it will override
	 * inspecting the $_POST and $_GET collections for keyword values.
	 */
	public function collectFromInlineInput($src=null)
	{
		$this->id->collectFromInput(null, $src);
	}

	/**
	 * Collects keyword terms from http request and stores them as separate Keyword objects in the object's
	 * $keywords property.
	 * @param array|null[optional] $src Optional array container of request variables. If specified, it will override
	 * inspecting the $_POST and $_GET collections for keyword values.
	 */
	public function collectKeywordInput($src=null)
	{
		$this->clearKeywordData();
		if ($src===null) {
			if (array_key_exists($this->keywordInput->key, $_POST)) {
				$src = $_POST[$this->keywordInput->key];
			}
			elseif (array_key_exists($this->keywordInput->key, $_POST)) {
				$src = $_GET[$this->keywordInput->key];
			}
			else {
				return;
			}
		}
		$keywords = array();
		if (array_key_exists($this->keywordInput->key, $src)) {
			$keywords = $this->extractKeywordTerms($src[$this->keywordInput->key]);
		}
		foreach($keywords as $term) {
			array_push($this->keywords, new Keyword(trim($term), $this->id->value, $this->siteSection->id->value));
		}
	}

	/**
	 * Removes the record from the database that corresponds to the object's id value. Also removes any records linked
	 * to that main record.
	 * @return string String containing a description of the results of the deletion.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function delete()
	{
		$status = parent::delete();
		$status .= $this->deleteKeywords();
		return ($status);
	}

	/**
	 * Deletes any keyword records linked to the main content record represented by the object.
	 * @return string String containing a description of the results of the deletion.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function deleteKeywords()
	{
		$this->testForParentID();
		$this->testForContentType();

		$this->connectToDatabase();
		$query = "CALL keywordDeleteLinked (".
			$this->id->escapeSQL($this->mysqli).",".
			$this->siteSection->id->escapeSQL($this->mysqli).")";
		$this->query($query);
		return ("All linked keyword records were deleted.");
	}

	/**
	 * Extract keyword terms from a comma-delimited string containing multiple terms.
	 * @param string $src Comma-delimited series of keyword terms.
	 * @return array Keyword terms separated out into an array.
	 */
	protected function extractKeywordTerms($src) {

		if (is_array($src)) {
			$terms = array_values($src);
		}
		else {
			$terms = array_unique(explode(',', $src));
		}
		$terms = array_filter($terms, function($term) { return(strlen($term) > 0);});
		array_walk($terms, function(&$term) {
			$term = trim(filter_var($term, FILTER_SANITIZE_STRING));
		});
		return ($terms);
	}

	/**
	 * Formats a comma-delimited string out of the object's keywords.
	 * @param bool[optional] $fetch_from_database If TRUE return keywords from database. If FALSE return keyword terms
	 * currently stored in the object properties. Defaults to TRUE.
	 * @return string Comma-delimited string containing all of the current keywords associated with this record.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function formatKeywordList($fetch_from_database=true)
	{
		if ($fetch_from_database && $this->hasData()) {
			$this->readKeywords();
		}
		return(stripslashes(join(', ', array_map('self::termCallback', $this->keywords))));
	}

	/**
	 * Returns markup containing keywords as links to listings filtered by the keyword value.
	 * @param array $context[optional] Array containing variables to insert into the template.
	 * @return string|false Markup to be used to display the keywords. False on error retrieving markup content.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\ResourceNotFoundException
	 */
	public function formatKeywordListPageContent($context=array())
	{
		if ($this->hasData()) {
			$this->readKeywords();
		}
		$context['keywords'] = $this;
		return (PageContent::loadTemplateContent(static::$keywordListTemplate, $context));
	}

	/**
	 * Returns an array containing just the keyword terms as strings for each keyword linked to the record in the database.
	 * @param bool[optional] $fetch_from_database If TRUE return keywords from database. If FALSE return keyword terms
	 * currently stored in the object properties. Defaults to TRUE.
	 * @return array List of keyword terms currently linked to the record in the database.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function getKeywordTermsArray( $fetch_from_database=true )
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
	public function hasKeywordData()
	{
		if (strlen($this->keywordInput->value) > 0) {
			return (true);
		}
		if (is_array($this->keywords)) {
			foreach($this->keywords as &$keyword) {
				if (strlen($keyword->term->value) > 0) {
					return (true);
				}
			}
		}
		return (false);
	}

	/**
	 * Retrieve record data.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read()
	{
		parent::read();
		$this->siteSection->read();
		$this->readKeywords();
	}

	/**
	 * Retrieves keywords linked to the current record.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function readKeywords()
	{
		$this->testForParentID();
		$this->testForContentType();

		$this->connectToDatabase();
		$query = "CALL keywordSelectLinked(".
			$this->id->escapeSQL($this->mysqli).",".
			$this->siteSection->id->escapeSQL($this->mysqli).")";
		$data = $this->fetchRecords($query);

		foreach($data as $row) {
			$i = count($this->keywords);
			$this->keywords[$i] = new Keyword($row->term, $this->id->value, $this->siteSection->id->value, $row->count);
		}
	}

	/**
	 * Commits object property data to record in the database.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function save()
	{
		parent::save();
		$this->saveKeywords();
		ContentCache::updateKeywords($this->id->value, $this->siteSection);
	}

	/**
	 * Saves all keywords linked to the main record object.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function saveKeywords()
	{
		$this->deleteKeywords();
		foreach($this->keywords as &$keyword) {
			/** @var Keyword $keyword */
			$keyword->parent_id->value = $this->id->value;
			$keyword->save();
		}
	}

	/**
	 * Returns the term value of the Keyword object.
	 * @param Keyword $keyword Keyword object containing a keyword term.
	 * @return string Keyword term.
	 */
	protected static function termCallback($keyword)
	{
		return($keyword->term->value);
	}

	/**
	 * Validates the internal property values of the object for data that is not valid.
	 * Updates the $validation_errors property of the object with messages describing the invalid values.
	 * @param array[optional] $exclude_properties Collection of variable names to ignore in the request data.
	 * @throws ContentValidationException Errors found in the form data.
	 */
	public function validateInput($exclude_properties = [])
	{
		try {
		    /* bypass validation of site section properties */
		    array_push($exclude_properties, 'siteSection');
			parent::validateInput($exclude_properties);
		}
		catch (ContentValidationException $ex) {
			; /* continue validating collected request data */
		}
		try {
		    /* validate the content type id to ensure this record has a content type value */
		    $this->siteSection->id->validate();
        }
        catch(ContentValidationException $ex) {
            array_push($this->validationErrors, $ex->getMessage());
        }
        /** @var \Littled\Keyword\Keyword $keyword */
        foreach($this->keywords as &$keyword) {
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
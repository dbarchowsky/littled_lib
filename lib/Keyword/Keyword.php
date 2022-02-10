<?php
namespace Littled\Keyword;


use Exception;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\PageContent\Serialized\SerializedContentValidation;
use Littled\Request\IntegerInput;
use Littled\Request\StringTextarea;

/**
 * Class Keyword
 * Handles keyword values associated with site content.
 * @package Littled\Keyword
 */
class Keyword extends SerializedContentValidation
{
	/** @var string Parent input variable name. */
	const PARENT_PARAM = 'kwpi';
	/** @var string Keyword type input variable name. */
	const TYPE_PARAM = 'kwti';
	/** @var string Keyword input variable name. */
	const KEYWORD_PARAM = 'kwtx';
	/** @var string Keyword filter variable name. */
	const FILTER_PARAM = 'flkw';

	/** @var StringTextarea Keyword term. */
	public $term;
	/** @var IntegerInput Keyword type id. */
	public $type_id;
	/** @var IntegerInput Keyword parent id. */
	public $parent_id;
	/** @var string Keyword type name. */
	public $type;
	/** @var int Keyword count. */
	public $count;

	/**
	 * Keyword constructor.
	 * @param string $keyword Keyword term.
	 * @param ?int $parent_type_id Parent id.
	 * @param ?int $type_id Keyword type id.
	 * @param int $count (Optional) Keyword count. Defaults to 0.
	 */
	function __construct(string $keyword, ?int $parent_type_id=null, ?int $type_id=null, int $count=0 )
	{
		parent::__construct();
		$this->term = new StringTextarea("Keyword", Keyword::KEYWORD_PARAM, true, $keyword, 1000, null);
		$this->type_id = new IntegerInput("Keyword type", Keyword::TYPE_PARAM, true, $type_id);
		$this->parent_id = new IntegerInput("Parent", Keyword::PARENT_PARAM, true, $parent_type_id);
		$this->count = $count;
	}

    /**
     * Deletes Keyword record from database.
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws Exception
     */
	public function delete(): string
	{
		if (!$this->hasData()) {
			return('');
		}
		$this->connectToDatabase();
		$query = "CALL keywordDelete(".
			$this->term->escapeSQL($this->mysqli).",".
			$this->type_id->escapeSQL($this->mysqli).",".
			$this->parent_id->escapeSQL($this->mysqli).")";
		$this->query($query);
		return ("The keyword \"{$this->term->value}\" was successfully deleted.");
	}

    /**
     * Checks if the search term already exists in the database.
     * @return bool True/false depending on whether the term already exists in the database for its parent.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws Exception
     */
	public function exists(): bool
	{
		$this->connectToDatabase();
		$query = "CALL keywordLookup(".
			$this->term->escapeSQL($this->mysqli).",".
			$this->type_id->escapeSQL($this->mysqli).",".
			$this->parent_id->escapeSQL($this->mysqli).")";
		$data = $this->fetchRecords($query);
		return ($data[0]->match_count>0);
	}

	/**
	 * Checks if a valid keyword term is currently stored in the object.
	 * @return bool True/false depending on whether the object contains a search term.
	 */
	public function hasData(): bool
	{
		return (strlen($this->term->value) > 0 &&
			$this->type_id->value > 0 &&
			$this->parent_id->value > 0);
	}

    /**
     * Saves keywords to the database.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws Exception
     */
	public function save(): void
	{
		if ($this->hasData()===false) {
			return;
		}
		$this->connectToDatabase();
		$query = "CALL keywordInsert(".
			$this->term->escapeSQL($this->mysqli).",".
			$this->type_id->escapeSQL($this->mysqli).",".
			$this->parent_id->escapeSQL($this->mysqli).")";
		$this->query($query);
	}
}
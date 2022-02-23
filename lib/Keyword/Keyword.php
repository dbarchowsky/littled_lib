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
	const PARENT_KEY = 'kwpi';
	/** @var string Keyword type input variable name. */
	const TYPE_KEY = 'kwti';
	/** @var string Keyword input variable name. */
	const KEYWORD_KEY = 'kwtx';
	/** @var string Keyword filter variable name. */
	const FILTER_KEY = 'flkw';

	/** @var StringTextarea Keyword term. */
	public StringTextarea $term;
	/** @var IntegerInput Keyword type id. */
	public IntegerInput $type_id;
	/** @var IntegerInput Keyword parent id. */
	public IntegerInput $parent_id;
	/** @var string Keyword type name. */
	public string $type;
	/** @var int Keyword count. */
	public int $count;

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
		$this->term = new StringTextarea("Keyword", Keyword::KEYWORD_KEY, true, $keyword, 1000, null);
		$this->type_id = new IntegerInput("Keyword type", Keyword::TYPE_KEY, true, $type_id);
		$this->parent_id = new IntegerInput("Parent", Keyword::PARENT_KEY, true, $parent_type_id);
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
		$this->query('CALL keywordDelete(?,?,?)', 'sii', $this->term->value, $this->type_id->value, $this->parent_id->value);
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
		$data = $this->fetchRecords("CALL keywordLookup(?,?,?)", 'sii', $this->term->value, $this->type_id->value, $this->parent_id->value);
		return ($data[0]->match_count>0);
	}

	/**
	 * @inheritDoc
	 */
	public function hasData(): bool
	{
		return (strlen($this->term->value) > 0 &&
			$this->type_id->value > 0 &&
			$this->parent_id->value > 0);
	}

    /**
     * Commits keyword data to the database
     * @return void
     * @throws Exception
     */
	public function save(): void
	{
		if ($this->hasData()===false) {
			return;
		}
		$this->query('CALL keywordInsert(?,?,?)', 'sii', $this->term->value, $this->type_id->value, $this->parent_id->value);
	}
}
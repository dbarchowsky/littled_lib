<?php
namespace Littled\Request;

use mysqli;
use Littled\Validation\Validation;

class FloatInput extends RenderedInput
{
	/** @var string Form input element template filename */
	protected static string $input_template_filename = 'string-text-input.php';
	/** @var string */
	protected static string $template_filename = 'string-text-field.php';
    /** @var int */
    const DEFAULT_DATA_SIZE = 16;

    public function __construct(string $label, string $key, bool $required = false, $value = null, int $size_limit = 0, ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
        $this->content_type = 'number';
    }

    /**
	 * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
	 * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 * @param ?string $key Key to use in place of the internal $key property value.
	 */
	public function collectRequestData(?array $src = null, ?string $key=null)
	{
		if ($this->bypass_collect_request_data===true) {
			return;
		}
		$this->value = Validation::collectNumericRequestVar((($key)?:($this->key)), null, $src);
	}

	/**
	 * @inheritDoc
	 */
	public function collectAjaxRequestData(object $data)
	{
		parent::collectAjaxRequestData($data);
		$this->value = Validation::parseNumeric($this->value);
	}

	/**
	 * @inheritDoc
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=false): ?string
	{
		$value = Validation::parseNumeric($this->value);
		if ($value===null) {
			return(null);
		}
		return ($mysqli->real_escape_string($value));
	}

    public static function getPreparedStatementTypeIdentifier(): string
    {
        return 'd';
    }

	/**
	 * @param integer $value Value to assign as the value of the object.
	 */
	public function setInputValue($value)
	{
		$this->value = Validation::parseNumeric($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate()
	{
		if (($this->isEmpty()===false) && (Validation::parseNumeric($this->value)===null)) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
		parent::validate();
	}
}
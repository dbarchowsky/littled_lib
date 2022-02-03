<?php
namespace Littled\Request;

use mysqli;
use Littled\Validation\Validation;

/**
 * Class IntegerInput
 * @package Littled\Request
 */
class IntegerInput extends RenderedInput
{
    /** @var string Form input element template filename */
    protected static $input_template_filename = 'string-text-input.php';
    /** @var string */
    protected static $template_filename = 'string-text-field.php';
    /** @var int */
    const DEFAULT_DATA_SIZE = 8;

    public function __construct(string $label, string $key, bool $required = false, $value = null, int $size_limit = 0, ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
        $this->contentType = 'number';
    }

    /**
	 * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
	 * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 * @param ?string $key Key to use in place of the internal $key property value.
	 */
	public function collectRequestData(?array $src=null, ?string $key=null)
	{
		if ($this->bypassCollectPostData===true) {
			return;
		}
		$this->value = Validation::collectIntegerRequestVar((($key)?:($this->key)), null, $src);
	}

	/**
	 * Assigns property value from corresponding value in JSON data passed along with a client request.
	 * @param object $data
	 */
	public function collectJsonRequestData(object $data)
	{
		parent::collectJsonRequestData($data);
		$this->value = Validation::parseInteger($this->value);
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param mysqli $mysqli
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to FALSE.
	 * @return string Escaped value.
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=false): string
	{
		$value = Validation::parseInteger($this->value);
		if ($value===null) {
			return('NULL');
		}
		return ($mysqli->real_escape_string($value));
	}

    /**
     * {@inheritDoc}
     */
    public function safeValue($options=[]): string
    {
        if (!is_numeric($this->value)) {
            return ('');
        }
        return parent::safeValue($options);
    }

    /**
	 * @param int $value Value to assign as the value of the object.
	 */
	public function setInputValue($value)
	{
		$this->value = Validation::parseInteger($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate()
	{
		if ((false === $this->isEmpty()) && (null === Validation::parseInteger($this->value))) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
		parent::validate();
	}
}
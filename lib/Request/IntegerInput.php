<?php
namespace Littled\Request;

use mysqli;
use Littled\Validation\Validation;

/**
 * Class IntegerInput
 * @package Littled\Request
 */
class IntegerInput extends RequestInput
{
    /** @var int */
    const DEFAULT_DATA_SIZE = 8;

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
    public function safeValue(?int $options = null): string
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
	 * Render the form input element(s) in the DOM.
	 * @param ?string $label String to use as input label. If this value is not provided, the object's
	 * $label property value will be used. Defaults to NULL.
	 * @param ?string $css_class CSS class name(s) to apply to the input container.
	 * @param array $options Extra attributes and attribute values to apply to the form input element.
	 */
	public function render( ?string $label=null, ?string $css_class=null, array $options=[] )
	{
		print ("<span class='\"alert alert-warning\">IntegerInput::renderInput() )Not implemented.</span></div>");
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate()
	{
		if (($this->isEmpty()===false) && (Validation::parseInteger($this->value)===null)) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
		parent::validate();
	}
}
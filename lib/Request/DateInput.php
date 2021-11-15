<?php
namespace Littled\Request;

use DateTime;
use mysqli;
use Littled\Exception\ContentValidationException;

/**
 * Class DateInput
 * @package Littled\Request
 */
class DateInput extends StringInput
{
	/**
	 * DateInput constructor.
	 * @param string $label Input label
	 * @param string $param value of the name attribute of the input
	 * @param boolean $required (Optional) flag indicating if this form data is required. Defaults to FALSE.
	 * @param string $value (Optional) initial value of the input. Defaults to NULL.
	 * @param int $size_limit  (Optional) maximum size in bytes of the value when it is stored in the database (for strings). Defaults to 0.
     * @param ?int $index Position of this form input within a series of similar inputs.
	 */
	function __construct(
		string$label,
		string $param,
		bool $required = false,
		$value = null,
		int $size_limit = 20,
		?int $index=null
	)
	{
		parent::__construct($label, $param, $required, $value, $size_limit, $index);
	}

	/**
	 * Collects date value from JSON request data.
	 * @param object $data Data collects containing the date value.
	 * @throws ContentValidationException
	 */
	public function collectJsonRequestData(object $data)
	{
		parent::collectJsonRequestData($data);
		if (strlen("".$this->value)>0) {
			$this->setDateValue();
		}
	}

	/**
	 * Validates the date value.
	 * @param string $date_format (Optional) date format to apply to the date value. Default value is 'Y-m-d H:i:00'.
	 * @throws ContentValidationException Date value is missing when required or is in an unrecognized format.
	 */
	public function validate( string $date_format='Y-m-d H:i:00' )
	{
		if (true === $this->required && (null === $this->value || strlen($this->value) < 1)) {
			throw new ContentValidationException("$this->label is required.");
		}
		if (false === $this->required && (null === $this->value || strlen($this->value) < 1)) {
			return;
		}
		if (strlen($this->value) > $this->sizeLimit) {
			throw new ContentValidationException("$this->label is limited to $this->sizeLimit character".(($this->sizeLimit!=1)?("s"):("")).".");
		}
		$this->setDateValue($date_format);
	}

	/**
	 * Returns a string to use to save the object's value to a database record.
	 * @param mysqli $mysqli Database connection to use for its escape_string() routine.
	 * @param bool[optional] $include_quotes If TRUE, the escape string will be enclosed in quotes. Defaults to TRUE.
	 * @return string Escaped value.
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=true): string
	{
        if ($this->value=="") {
            return ("NULL");
        }
        $ts = strtotime($this->value);
        if ($ts !== false) {
            $date_string = date("Y-m-d H:i:s",$ts);
        }
        else {
            $date = DateTime::createFromFormat('d/m/Y', $this->value);
            if ($date!==false) {
                $date_string = $date->format('Y-m-d');
            }
            else {
                /* maybe it's in YYYY-MM-DD format, just send it back whatever it is */
                $date_string = $this->value;
            }
        }
        return((($include_quotes)?("'"):("")).$mysqli->real_escape_string($date_string).(($include_quotes)?("'"):("")));
	}

	/**
	 * Returns the current value of the object as formatted string value.
	 * @param string $date_format (Optional) date format to apply to the date value. Default value is 'Y-m-d H:i:00'.
     * @return ?string Formatted date string.
	 * @throws ContentValidationException Current value not a valid date value.
     */
	public function formatDateValue( string $date_format='Y-m-d H:i:00' ): ?string
	{
		$valid = (strtotime($this->value)!==false);
		if (!$valid) {
			$valid = (DateTime::createFromFormat('d/m/Y', $this->value) !== false);
		}
		if (!$valid) {
			$valid = (DateTime::createFromFormat('Y-m-d', $this->value) !== false);
		}
		if (!$valid) {
			throw new ContentValidationException("$this->label is not in a recognized date format.");
		}
		if ($date_format !== null) {
			return (date($date_format, strtotime($this->value)));
		}
		return $this->value;
	}

    /**
     * Converts the current value of the object to a standard date format.
     * @param string $date_format (Optional) date format to apply to the date value. Default value is 'Y-m-d H:i:00'.
     * @throws ContentValidationException Current value not a valid date value.
     */
	protected function setDateValue( string $date_format='Y-m-d H:i:00' )
    {
        if (strlen($date_format) > 0) {
            $this->value = $this->formatDateValue($date_format);
        }
    }

	/**
	 * Assigns a value to the object after parsing the value in order to be in a workable format.
	 * @param string $value Value to assign to the object.
	 * @param string $date_format (Optional) date format to apply to the date value. Default value is 'Y-m-d'.
	 */
	public function setInputValue($value, string $date_format='Y-m-d')
	{
		parent::setInputValue($value);
		try {
			$this->setDateValue('Y-m-d');
		}
		catch(ContentValidationException $ex) {
			$this->value = '';
		}
	}
}
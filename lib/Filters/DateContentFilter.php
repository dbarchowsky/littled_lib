<?php
namespace Littled\Filters;

use Littled\Validation\Validation;
use Littled\Exception\ContentValidationException;
use DateTime;
use Exception;
use mysqli;

/**
 * Class DateContentFilter
 * @package Littled\Filters
 */
class DateContentFilter extends StringContentFilter
{
    function __construct(string $label, string $key, $value = null, $size = 0, $cookieKey = '')
    {
        parent::__construct($label, $key, $value, $size, $cookieKey);
        $this->checkEmptyValue();
    }

    /**
     * Converts empty string value to null. Date value passed to SQL query cannot be an empty string.
     * @return void
     */
    protected function checkEmptyValue()
    {
        if ($this->value==='') {
            $this->value = null;
        }
    }

    /**
	 * @inheritDoc
	 */
	public function collectValue(bool $read_cookies = true, ?array $src=null)
	{
		parent::collectValue($read_cookies, $src);
		if ($this->value) {
			try {
				$d = Validation::validateDateString($this->value);
				$this->value = $d->format("m/d/Y");
			}
			catch (ContentValidationException $ex) {
				$this->value = "[".$ex->getMessage()."]";
			}
		}
        $this->checkEmptyValue();
	}

	/**
	 * Escapes date string to format expected in SQL statements.
	 * @param mysqli $mysqli
	 * @param bool $include_quotes (Optional) If TRUE, the escape string will be enclosed in quotes. Defaults to TRUE.
     * @param bool $include_wildcards
	 * @return string
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=true, bool $include_wildcards=false): string
	{
		if ($this->value===null) {
			return ('NULL');
		}
		if ($this->value=='') {
			return ('NULL');
		}
		try {
			$dt = new DateTime($this->value);
		}
		catch(Exception $e) {
			return ('NULL');
		}
		$value = $dt->format('Y-m-d');
		return ((($include_quotes)?("'"):("")).$value.(($include_quotes)?("'"):("")));
	}
}
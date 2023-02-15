<?php
namespace Littled\API;


/**
 * Class JSONField
 * @package Littled\PageContent\API
 */
class JSONField
{
	const FORMAT_CURRENCY = 'CURRENCY';

	/** @var string Field name. */
	public $name;
	/** @var mixed|string Field value. */
	public $value;
	/** @var string $format */
	public $format;

	/**
	 * JSONField constructor.
	 * @param mixed $name JSON field key.
	 * @param mixed $value JSON field value.
	 * @param mixed $format Enum indicating format to use when converting object to JSON data.
	 * Acceptable values are 'CURRENCY'. Leave blank for no formatting.
	 */
	function __construct ($name='', $value='', $format='')
	{
		$this->name = $name;
		$this->value = $value;
		$this->format = $format;
	}

	/**
	 * Escapes HTML characters to avoid JSON parsing errors.
	 * @param string $src String to be escaped.
	 * @return string Escaped version of the string.
	 */
	public static function escapeHTML( string $src ):string
	{
		return (str_replace("&","&amp;",str_replace(">","&gt;",str_replace("<","&lt;",utf8_encode($src)))));
	}

	/**
	 * Adds the current key/value pair to the supplied array.
	 * @param array $data Array containing full set of JSON key/value pairs to be passed back to the client.
	 */
	public function formatJSON( array &$data )
	{
        $func = function($i) {
          if ($i instanceof JSONResponseBase) {
              return ((object)$i->formatJson());
          }
          else {
              return ($i);
          }
        };

		$val = $this->value;
		if (is_array($val)) {
			$val = array_map($func, $val);
		}
		if ($this->format==JSONField::FORMAT_CURRENCY && is_numeric($val)) {
			$val = number_format($val, 2);
		}
		$data[$this->name] = $val;
	}
}
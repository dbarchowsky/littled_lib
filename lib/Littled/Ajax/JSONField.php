<?php
namespace Littled\Ajax;


/**
 * Class JSONField
 * @package Littled\PageContent\Ajax
 */
class JSONField
{
	/** @var string Field name. */
	var $name;
	/** @var mixed|string Field value. */
	var $value;


	/**
	 * class constructor
	 *
	 * @param string|null[optional] $name JSON field key.
	 * @param mixed[optional] $value JSON field value.
	 *
	 */
	function __construct ($name='', $value='')
	{
		$this->name = $name;
		$this->value = $value;
	}


	/**
	 * Adds the current key/value pair to the supplied array.
	 * @param array $data Array containing full set of JSON key/value pairs to be passed back to the requestee.
	 */
	public function formatJSON( &$data )
	{
		$val = $this->value;
		if (is_array($val)) {
			$val = implode(',', $val);
		}
		$data[$this->name] = $val;
	}


	/**
	 * Escapes HTML characters to avoid JSON parsing errors.
	 * @param string $src String to be escaped.
	 * @return string Escaped version of the string.
	 */
	public static function escapeHTML( $src )
	{
		return (str_replace("&","&amp;",str_replace(">","&gt;",str_replace("<","&lt;",utf8_encode($src)))));
	}
}
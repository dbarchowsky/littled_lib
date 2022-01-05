<?php
namespace Littled\Filters;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;
use mysqli;


/**
 * Class ContentFilter
 * @package BFHHand\Filters
 */
class ContentFilter
{
	/** @var string Key of the cookie element holding the filter value. */
	public $cookieKey;
	/** @var string Variable name used to pass along filter values. */
	public $key;
	/** @var string Label to display on filter form inputs. */
	public $label;
	/** @var int Size limit of the filter value. */
	public $size;
	/** @var mixed|string Filter value. */
	public $value;

	/**
	 * ContentFilter constructor.
	 * @param string $label Label to display on filter form inputs.
	 * @param string $key Variable name used to pass along filter values.
	 * @param ?mixed $value Filter value.
	 * @param ?mixed $size Size limit of the filter value.
	 * @param ?mixed $cookieKey Key of the cookie element holding the filter value.
	 */
	function __construct(string $label, string $key, $value=null, $size=0, $cookieKey='')
	{
		$this->label = $label;
		$this->key = $key;
		$this->value = $value;
		$this->size = $size;
		$this->cookieKey = $cookieKey;
	}

	/**
	 * Clears the filter value from cookies.
	 */
	protected function clearCookie()
	{
		if (isset($this->cookieKey)) {
			if (isset($_COOKIE[$this->cookieKey])) {
				$expires = time()+3600*24*90;
				$ar = explode("|", $_COOKIE[$this->cookieKey]);
				if (array_key_exists($this->key, $ar)) unset($ar[$this->key]);
				setcookie($this->cookieKey, implode("|", $ar), $expires);
			}
			else {
				setcookie($this->cookieKey, '', time()-1);
			}
		}
		else {
			setcookie($this->key, '', time()-1);
		}
	}

	/**
	 * collects filter value from request variables (GET or POST).
	 */
	protected function collectRequestValue()
	{
		$value = Validation::collectRequestVar($this->key);
		$this->value = $value ?: $this->value;
	}

	/**
	 * Collects the filter value from request variables, session variables, or cookie variables, in that order.
	 * @param bool $read_cookies Flag indicating that the cookie collection should be included in the search for a
	 * filter value.
	 */
	public function collectValue(bool $read_cookies=true)
	{
		$this->collectRequestValue();
		if ($this->value) {
			return;
		}

		if (isset($_SESSION[$this->key])) {
			$this->value = trim(''.$_SESSION[$this->key]);
			return;
		}

		if ($read_cookies && $this->cookieKey) {
			if (isset($_COOKIE[$this->cookieKey])) {
				$ar = explode('|', $_COOKIE[$this->cookieKey]);
				if (array_key_exists($this->key, $ar)) {
					$this->value = $ar[$this->key];
				}
			} elseif (isset($_COOKIE[$this->key])) {
				$this->value = $_COOKIE[$this->key];
			}
		}
	}

	/**
	 * Escapes the object's value property for inclusion in SQL queries.
	 * @param mysqli $mysqli Database connection.
	 * @param bool $include_quotes (Optional) If TRUE, the escape string will be enclosed in quotes. Defaults to TRUE.
	 * @return string Escaped value.
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=true): string
	{
		if ($this->value===null) {
			return ("null");
		}
		if ($this->value===true) {
			return ('1');
		}
		if ($this->value===false) {
			return ('0');
		}
		return (($include_quotes)?("'"):("")).$mysqli->real_escape_string($this->value).(($include_quotes)?("'"):(""));
	}
	
	/**
	 * Returns name/value pair from within query string representing the
	 * current "param" and "value" property values of the object.
	 * @return string Name/value pair formatted for insertion into a query string.
	 */
	function formatQueryString( ): string
	{
		if (strlen($this->value)>0) {
			return("$this->key=".urlencode($this->value));
		}
		return ('');
	}

	/**
	 * Saves the filter value as a cookie variable.
	 */
	protected function saveCookie()
	{
		$expires = time()+3600*24*90;
		if (isset($this->cookieKey)) {
			if (isset($_COOKIE[$this->cookieKey])) {
				$ar = explode("|", $_COOKIE[$this->cookieKey]);
				$ar[$this->key] = $this->value;
				setcookie($this->cookieKey, implode("|", $ar), $expires);
			}
			else {
				setcookie($this->cookieKey, $this->key."|".$this->value, $expires);
			}
		}
		else {
			setcookie($this->key, $this->value, $expires);
		}
	}

	/**
	 * Output markup that will preserve the filter's value in an HTML form.
	 * @throws ResourceNotFoundException
	 */
	public function saveInForm()
	{
		ContentUtils::renderTemplate(RequestInput::getTemplateBasePath()."hidden-input.php", array(
			'key' => $this->key,
			'index' => '',
			'value' => $this->value
		));
	}
}

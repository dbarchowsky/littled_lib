<?php
namespace Littled\PageContent\Serialized;

use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Albums\Gallery;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;


/**
 * Class SerializedContentUtils
 * @package Littled\PageContent\Serialized
 */
class SerializedContentUtils extends MySQLConnection
{
	/** @var array Container for validation error messages. */
	public $validationErrors;
	/** @var string Error message returned when invalid form data is encountered. */
	public $validationMessage;

    /**
     * SerializedContentUtils constructor.
     */
	public function __construct()
    {
        parent::__construct();
        $this->validationErrors = [];
        $this->validationMessage = "Errors were found in the content.";
    }

    /**
	 * Returns the form data members of the objects as series of nested associative arrays.
	 * @param array[optional] $arExclude array of parameter names to exclude from the returned array.
	 * @return array Associative array containing the object's form data members as name/value pairs.
	 */
	public function arrayEncode ($exclude_keys=null )
	{
		$ar = array();
		foreach ($this as $key => $item) {
			if (is_object($item)) {
				if (!is_array($exclude_keys) || !in_array($key, $exclude_keys)) {
					if ($item instanceof RequestInput) {
						$ar[$key] = $item->value;
					}
					elseif ($item instanceof SerializedContent) {
						/** @var SerializedContent $item */
						$ar[$key] = $item->arrayEncode();
					}
					elseif ($item instanceof Gallery) {
						/** @var \Littled\PageContent\Albums\Gallery $item */
						$ar[$key] = $item->arrayEncode(array("tn", "site_section"));
					}
				}
			}
		}
		return ($ar);
	}

	/**
	 * Assign values contained in array to object input properties.
	 * @param array $row Row returned by mysql query.
	 */
	protected function assignRecordValues( &$row )
	{
		$used_keys = array();
		foreach ($this as $key => &$item) {
			/** @var RequestInput $item */
			if ($this->isInput($key, $item, $used_keys)) {
				/* store value retrieved from database */
				if ($item->columnName) {
					$custom_key = $item->columnName;
					$item->setInputValue($row->$custom_key);
				}
				else {
					$item->setInputValue($row->$key);
				}
			}
		}
	}

	/**
	 * Clears the data container values in the object.
	 */
	public function clearValues( )
	{
		foreach ($this as $key => $item) {
			/** @var object $item */
			if(is_object($item) && method_exists($item, 'clearValue')) {
				$item->clearValue();
			}
			elseif(is_object($item) && method_exists($item, 'clearValues')) {
				$item->clearValues();
			}
		}
	}

	/**
	 * Set property values using input variable values, e.g. GET, POST, cookies
	 * @param array[optional] $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectFromInput($src=null)
	{
		foreach($this as $key => $item) {
			if (is_object($item) && method_exists($item, 'collectFromInput')) {
				if (!property_exists($item, 'bypassCollectFromInput') || $item->bypassCollectFromInput===false) {
					$item->collectFromInput(null, $src);
				}
			}
		}
	}

	/**
	 * Copies the property values from one object into this instance.
	 * @param mixed $src Object to use to copy values over to this object.
	 * @throws InvalidTypeException Source is not a valid object.
	 */
	public function copy( $src )
	{
		if (!is_object($src)) {
			throw new InvalidTypeException("Source for copy is not an object.");
		}
		if (get_class($this) != get_class($src)) {
			throw new InvalidTypeException("Invalid object for copy.");
		}
		foreach (get_object_vars($src) as $key => $value) {
			if ((is_object($value)) && ($value instanceof RequestInput)) {
				$this->$key->value = $value->value;
			}
			elseif((is_object($this->$key)) && method_exists($this->$key, 'copy')) {
				$this->$key->copy($value);
			}
			elseif(!is_object($value)) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Returns a list of column names to use to format SQL queries that will be used to read and update
	 * records.
	 * @param array[optional] $used_keys Properties that have already been added to the stack.
	 * @return array Key/value pairs for each RequestInput property of the class.
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 */
	protected function formatDatabaseColumnList($used_keys=[])
	{
		$fields = array();
		foreach ($this as $key => $item) {
			if ($this->isInput($key, $item, $used_keys)) {
				if ($item->isDatabaseField===false) {
					continue;
				}
				/* format column name and value for SQL statement */
				if ($item->columnName) {
					$fields[$item->columnName] = $this->escapeSQLValue($item->value);
				} else {
					$fields[$key] = $this->escapeSQLValue($item->value);
				}
			}
		}
		return ($fields);
	}

	/**
	 * Checks if the class property is an input object and should be used for
	 * various operations such as updating or retrieving data from the database,
	 * or retrieving data from forms.
	 * @param string $key Name of the class property.
	 * @param mixed $item Value of the class property.
	 * @param array $used_keys Array containing a list of the objects that
	 * have already been listed as input properties.
	 * @return boolean True if the object is an input class and should be used to update the database. False otherwise.
	 */
	protected function isInput(&$key, &$item, &$used_keys)
	{
		$is_input = (($item instanceof RequestInput) &&
			($key != "id") &&
			($key != "index") &&
			($item->isDatabaseField==true));
		if ($is_input) {
			/* Check if this item has already been used as in input property.
			 * This prevents references used as aliases of existing properties
			 * from being included in database queries.
			 */
			if (in_array($item->key, $used_keys)) {
				$is_input = false;
			}
			else {
				/* once an input property is marked as such, track it so it
				 * can't be included again.
				 */
				$used_keys[] = $item->key;
			}
		}
		return ($is_input);
	}

	/**
	 * Fills object properties using property values found in $src argument.
	 * @param array|object $src Source object containing values to assign to this instance.
	 */
	public function fill($src)
	{
		foreach ($src as $key => $val) {
			if (property_exists($this, $key)) {
				if ($this->$key instanceof RequestInput) {
					$this->$key->setInputValue($val);
				}
				elseif (!is_object($this->$key)) {
					$this->$key = $val;
				}
			}
		}
	}

	/**
	 * Checks of SECTION_ID has been defined as a constant of the class and returns its value if it has.
	 * @return integer Class's content type id value, if it has been defined.
	 */
	public function getContentTypeID()
	{
		$content_type_const = get_class($this)."::SECTION_ID";
		return ((defined($content_type_const))?(constant($content_type_const)):(null));
	}

	/**
	 * Return the form data members of the object as a JSON string.
	 * @param array[optional] $exclude_keys Array of property names to exclude from the encoding.
	 * @return string JSON-encoded name/value pairs extracted from the object.
	 */
	public function jsonEncode ($exclude_keys=null)
	{
		return (json_encode($this->arrayEncode($exclude_keys)));
	}

	/**
	 * Returns an appropriate label given the value of $count if $count requires the label to be pluralized.
	 * @param integer $count Number determining if the label is plural or not.
	 * @param string $property_name Name of property to make plural.
	 * @return string Plural form of the record label if $count is not 1.
	 */
	public function pluralLabel( $count, $property_name )
	{
		if (!property_exists($this, $property_name)) {
			return (null);
		}
		if ($this->{$property_name} instanceof StringInput === false) {
			return (null);
		}
		if ($this->{$property_name}->value === null || $this->{$property_name}->value === '') {
			return (null);
		}

		$label = strtolower($this->{$property_name}->value);
		if ($count==1) {
			return ($label);
		}
		elseif (substr($label, -1)=='y') {
			return (substr($label, 0, -1).'ies');
		}
		elseif (substr($label, -1)=='s') {
			return ($label);
		}
		else {
			return ($label.'s');
		}
	}

	/**
	 * Loads content from a template file. Writes the parsed content to a separate file.
	 * @param string $src_path Path to content template.
	 * @param string $dst_path Path to cache file.
	 * @param array[optional] $context Array containing name/value pairs representing variable names and values to insert into the source template at $src_path;
	 * @throws ResourceNotFoundException Cache template not found.
	 * @throws \Exception File error.
	 */
	function updateCacheFile ($src_path, $dst_path, $context=null)
	{
		if (!file_exists($src_path)) {
			throw new ResourceNotFoundException('Cache template not available.');
		}
		if (is_array($context)) {
			foreach($context as $key => $val) {
				${$key} = $val;
			}
		}
		ob_start();
		include ($src_path);
		$f = fopen($dst_path, "w");
		fputs($f, ob_get_contents());
		fclose($f);
		ob_end_clean();
	}

	/**
	 * Validates the internal property values of the object for data that is not valid.
	 * Updates the $validation_errors property of the object with messages describing the invalid values.
	 * @param array[optional] $exclude_properties Names of class properties to exclude from validation.
	 * @throws ContentValidationException Invalid content found.
	 */
	public function validateInput($exclude_properties=[])
	{
		$this->validationErrors = [];
		foreach($this as $key => $property) {
			if (in_array($key, $exclude_properties)) {
				continue;
			}
			if ($property instanceof RequestInput) {
				try {
					$property->validate();
				}
				catch(ContentValidationException $ex) {
					array_push($this->validationErrors, $ex->getMessage());
				}
			}
			elseif($property instanceof SerializedContentUtils) {
				try {
					$property->validateInput();
				}
				catch(ContentValidationException $ex) {
				    if (strlen($ex->getMessage()) > 0) {
                        array_push($this->validationErrors, $ex->getMessage());
                    }
					$this->validationErrors = array_merge($this->validationErrors, $property->validationErrors);
				}
			}
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException($this->validationMessage);
		}
	}
}
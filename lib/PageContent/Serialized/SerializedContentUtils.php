<?php
namespace Littled\PageContent\Serialized;

use Littled\Database\AppContentBase;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Albums\Gallery;
use Littled\PageContent\ContentUtils;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Exception;

/**
 * Class SerializedContentUtils
 * @package Littled\PageContent\Serialized
 */
class SerializedContentUtils extends AppContentBase
{
    /** @var string Path to CMS template dir */
    protected static string $common_cms_template_path;

	/** @var string[] Container for validation error messages. */
	public array $validationErrors=[];
	/** @var string Error message returned when invalid form data is encountered. */
	public string $validationMessage='';
	/** @var int */
	protected static int $content_type_id;
	/** @var string Path to cache template. */
	protected static string $cache_template = '';
	/** @var string Path to rendered cache file to use on site front-end. */
	protected static string $output_cache_file = '';

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
     * Add a separator string after a string.
     * @param string $str Source string.
     * @param string $separator (Optional) Character or string to append to the source string. Defaults to a comma.
     * @return string Modified string containing the separator.
     */
    public function appendSeparator(string $str, string $separator=','): string
    {
        if(strlen(trim($str)) > 0) {
            $str = rtrim($str)."$separator ";
        }
        return ($str);
    }

    /**
	 * Returns the form data members of the objects as series of nested associative arrays.
	 * @param array|null $exclude_keys (Optional) array of parameter names to exclude from the returned array.
	 * @return array Associative array containing the object's form data members as name/value pairs.
	 */
	public function arrayEncode (?array $exclude_keys=null ): array
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
						/** @var Gallery $item */
						$ar[$key] = $item->arrayEncode(array("tn", "site_section"));
					}
				}
			}
			elseif(is_array($item)) {
				$temp = [];
				foreach ($item as $element) {
					if ($element instanceof SerializedContent) {
						$temp[] = $element->arrayEncode($exclude_keys);
					}
				}
				$ar[$key] = $temp;
			}
		}
		return ($ar);
	}

	/**
	 * Clears the data container values in the object.
	 */
	public function clearValues( )
	{
		foreach ($this as $item) {
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
	 * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 */
	public function collectRequestData(?array $src=null)
	{
		foreach($this as $item) {
			if ( is_object($item) &&
				(!property_exists($item, 'bypassCollectFromInput') || $item->bypassCollectFromInput===false)) {
					if (method_exists($item, 'collectRequestData')) {
						$item->collectRequestData($src);
					}
					elseif (method_exists($item, 'collectFormInput')) {
						$item->collectFormInput(null, $src);
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
			if ($value instanceof RequestInput) {
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
	 * Returns a list of column names to use to format SQL queries that will be used to read and update
	 * records.
	 * @param array $used_keys (Optional) Properties that have already been added to the stack.
	 * @return array Key/value pairs for each RequestInput property of the class.
	 * @throws ConnectionException
	 * @throws ConfigurationUndefinedException
	 */
	protected function formatDatabaseColumnList(array $used_keys=[]): array
	{
        $this->connectToDatabase();
		$fields = array();
		foreach ($this as $key => $item) {
            /** @var RequestInput $item */
			if ($this->isInput($key, $item, $used_keys)) {
				if ($item->is_database_field===false) {
					continue;
				}
				/* format column name and value for SQL statement */
				if ($item->column_name) {
					$fields[$item->column_name] = $item->escapeSQL($this->mysqli);
				} else {
					$fields[$key] = $item->escapeSQl($this->mysqli);
				}
			}
		}
		return ($fields);
	}

	/**
	 * Returns cache template path.
	 * @return string Cache template path.
	 */
	public static function getCacheTemplatePath(): string
	{
		return (static::$cache_template);
	}

    /**
     * Returns current common cms template path value.
     * @return string Current common cms template path value.
     * @throws ConfigurationUndefinedException
     */
	public static function getCommonCMSTemplatePath(): string
    {
        if (static::$common_cms_template_path===null || strlen(static::$common_cms_template_path) < 1)
        {
            throw new ConfigurationUndefinedException("Path to shared content templates not set.");
        }
        return static::$common_cms_template_path;
    }

	/**
	 * Checks if content type id property exists and returns its value.
	 * @return ?int Class's content type id value, if it has been defined.
	 * @throws ConfigurationUndefinedException
	 */
	public static function getContentTypeId(): ?int
	{
		if (!isset(static::$content_type_id)) {
            throw new ConfigurationUndefinedException('Content type not set.');
        }
        return static::$content_type_id;
	}

    /**
     * Assign values contained in array to object input properties.
     * @param string $query SQL SELECT statement to use to hydrate object property values.
     * @throws RecordNotFoundException
     * @throws Exception
     */
	protected function hydrateFromQuery( string $query, string $types='', &...$vars )
	{
		if ($types) {
			array_unshift($vars, $query, $types);
			$data = call_user_func_array([$this, 'fetchRecords'], $vars);
		}
		else {
			$data = $this->fetchRecords($query);
		}
		if (count($data) < 1) {
			throw new RecordNotFoundException("Record not found.");
		}
		$this->hydrateFromRecordsetRow($data[0]);
	}

	/**
	 * Assign values contained in array to object input properties.
	 * @param object $row Recordset row containing values to copy into the object's properties.
	 */
	protected function hydrateFromRecordsetRow( object $row )
	{
		$used_keys = array();
		foreach ($this as $key => $item) {
			/** @var RequestInput $item */
			if ($this->isInput($key, $item, $used_keys)) {
				/* store value retrieved from database */
				if ($item->column_name) {
					$custom_key = $item->column_name;
					$item->setInputValue($row->$custom_key);
				}
				elseif(property_exists($row, $key)) {
					$item->setInputValue($row->$key);
				}
			}
		}
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
	protected function isInput(string $key, $item, array &$used_keys): bool
	{
		$is_input = (($item instanceof RequestInput) &&
			($key != "id") &&
			($key != "index") &&
			($item->is_database_field==true));
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
	 * Return the form data members of the object as a JSON string.
	 * @param array[optional] $exclude_keys Array of property names to exclude from the encoding.
	 * @return string JSON-encoded name/value pairs extracted from the object.
	 */
	public function jsonEncode ($exclude_keys=null): string
	{
		return (json_encode($this->arrayEncode($exclude_keys)));
	}

	/**
	 * Returns an appropriate label given the value of $count if $count requires the label to be pluralized.
	 * @param int $count Number determining if the label is plural or not.
	 * @param string $property_name Name of property to make plural.
	 * @return string Plural form of the record label if $count is not 1.
	 * @throws ConfigurationUndefinedException
     */
	public function pluralLabel( int $count, string $property_name ): string
	{
		if (!property_exists($this, $property_name)) {
			throw new ConfigurationUndefinedException(
				"Cannot get plural label for unknown property \"$property_name\" of ".get_class($this)
			);
		}
		if ($this->{$property_name} instanceof StringInput === false) {
			throw new ConfigurationUndefinedException(
				"Cannot get plural label for non-string input ".get_class($this)."::$property_name."
			);
		}
		if ($this->{$property_name}->value === null || $this->{$property_name}->value === '') {
			return '';
		}

		$label = $this->{$property_name}->value;
		if ($count===1) {
			return ($label);
		}
        else {
            return (static::makePlural($label));
        }
	}

    /**
     * Add a separator string before a string.
     * @param string $str Source string.
     * @param string $separator (Optional) Character or string to prepend to the source string. Defaults to a comma.
     * @return string Modified string containing the separator.
     */
    public function prependSeparator(string $str, string $separator=','): string
    {
        if(strlen(trim($str)) > 0) {
            $str = "$separator ".ltrim($str);
        }
        return ($str);
    }

	/**
	 * Save RequestInput property values in form markup.
	 * @param array $excluded_keys Optional list of keys that will be excluded from the form markup.
	 */
	public function preserveInForm(array $excluded_keys=[])
	{
		foreach($this as $item) {
			if ($item instanceof RequestInput && !in_array($item->key, $excluded_keys)) {
                // make sure to use template path for base object, which is a hidden input element
				$item->saveInForm(RequestInput::getTemplatePath());
			}
		}
	}

    /**
     * Sets value of shared cms templates path.
     * @param string $path Path to shared cms templates.
     */
    public static function setCommonCMSTemplatePath( string $path )
    {
        static::$common_cms_template_path = $path;
    }

	/**
	 * Loads content from a template file. Writes the parsed content to a separate file.
	 * @param array[optional] $context Array containing name/value pairs representing variable names and values to insert into the source template at $src_path;
	 * @param string|null[optional] $cache_template Path to content template. If not supplied, the internal $cache_template value will be used.
	 * @param string|null[optional] $output_cache_file Path to cache file. If not supplied, the internal $output_cache_file value will be used.
	 * @throws ResourceNotFoundException Cache template not found.
	 * @throws Exception File error.
	 */
	function updateCacheFile ($context=null, $cache_template=null, $output_cache_file=null)
	{
		if ($cache_template===null) {
			$cache_template = static::$cache_template;
			if (!file_exists($cache_template)) {
				throw new ResourceNotFoundException("External link cache template not available at \"$cache_template\".");
			}
		}
		if ($output_cache_file===null) {
			$output_cache_file = static::$output_cache_file;
		}
		$cache_content = ContentUtils::loadTemplateContent($cache_template, $context);
		$f = fopen($output_cache_file, "w");
		fputs($f, $cache_content);
		fclose($f);
	}
}
